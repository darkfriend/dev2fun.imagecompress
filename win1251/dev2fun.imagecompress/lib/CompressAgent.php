<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.11.4
 * @since 0.11.4
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Config\Option;
use CAgent;

IncludeModuleLangFile(__FILE__);

class CompressAgent
{
    const OPTION_NAME_AGENT = 'image_compress_agent';

    /**
     * Run agent
     * @return string
     */
    public static function agentRun()
    {
        try {
            $limit = Option::get(\Dev2funImageCompress::MODULE_ID, "cnt_step", 30);

            $filterList = [
                'COMRESSED' => 'N',
                '@CONTENT_TYPE' => [
                    'image/jpeg',
                    'image/png',
                    'application/pdf',
                ],
            ];

            $rsRes = Compress::getInstance()
                ->getFileList(
                    [],
                    $filterList,
                    $limit
                );
//            $rsRes->NavStart($limit, false);

            while ($arFile = $rsRes->GetNext()) {
                $strFilePath = \CFile::GetPath($arFile["ID"]);
                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $strFilePath)) {
                    Compress::getInstance()
                        ->addCompressTable(
                            $arFile['ID'],
                            [
                                'FILE_ID' => $arFile['ID'],
                                'SIZE_BEFORE' => 0,
                                'SIZE_AFTER' => 0,
                            ]
                        );
                    continue;
                }

                Compress::getInstance()
                    ->compressImageByID($arFile['ID']);
            }

        } catch (\Throwable $e) {
            $msg = "Error image optimization: {$e->getMessage()}";
            AddMessage2Log(
                $msg,
                \Dev2funImageCompress::MODULE_ID
            );
        }

        return self::class . '::agentRun();';
    }

    /**
     * Return agent id after add agent
     * @return int
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function addAgent(): int
    {
        $startTime = ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 600, 'FULL');
        $agentId = CAgent::AddAgent(
            self::class . '::agentRun();',
            \Dev2funImageCompress::MODULE_ID,
            'Y',
            600,
            '',
            'N',
            $startTime,
            100,
            false,
            false
        );
        if (!$agentId) {
            throw new \Exception('Error when add agent for cache-delete');
        }

        Option::set(\Dev2funImageCompress::MODULE_ID, self::OPTION_NAME_AGENT, $agentId);

        return $agentId;
    }

    /**
     * Return agent id from bitrix options
     * @return int|null
     */
    public static function getAgentIdOption(): ?int
    {
        return Option::get(\Dev2funImageCompress::MODULE_ID, self::OPTION_NAME_AGENT) ?: null;
    }

    /**
     * @return array|false|null
     */
    public static function getAgent()
    {
        static $agent = null;
        if ($agent === null) {
            $rs = CAgent::GetList([], ['NAME' => self::class . '::agentRun();']);
            $agent = $rs ? $rs->Fetch() : [];
        }

        return $agent;
    }

    /**
     * Return agent id from agents list
     * @return int|null
     */
    public static function getAgentIdDb(): ?int
    {
        $rs = self::getAgent();
        return $rs ? $rs['ID'] : null;
    }

    /**
     * @return int|null
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function getAgentId(): ?int
    {
        $agentId = self::getAgentIdOption();
        if (!$agentId) {
            $agentId = self::getAgentIdDb();
            if (!$agentId) {
                $agentId = self::addAgent();
            }
        }

        return $agentId;
    }

    /**
     * Activate agent
     * @return bool
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function activateAgent(): bool
    {
        return (bool)CAgent::Update(self::getAgentId(), ['ACTIVE' => 'Y']);
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function deactivateAgent(): bool
    {
        return (bool)CAgent::Update(self::getAgentId(), ['ACTIVE' => 'N']);
    }

    /**
     * @return string
     */
    public static function getAgentActiveValue(): string
    {
        return self::getAgent()['ACTIVE'] ?? 'N';
    }
}