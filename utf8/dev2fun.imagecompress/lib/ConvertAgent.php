<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.11.6
 * @since 0.11.4
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Config\Option;
use CAgent;

IncludeModuleLangFile(__FILE__);

class ConvertAgent
{
    const OPTION_NAME_AGENT = 'image_convert_agent';

    /**
     * Return agent id after add agent
     * @return int
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function addAgent(): int
    {
        $startTime = ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 60, 'FULL');
        $agentId = CAgent::AddAgent(
            LazyConvert::class . '::agentRun();',
            \Dev2funImageCompress::MODULE_ID,
            'Y',
            120,
            '',
            'Y',
            $startTime,
            100,
            false,
            false
        );
        if (!$agentId) {
            throw new \Exception('Error when add agent for cache-delete');
        }

        self::saveAgentIdOption($agentId);

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
     * Save id agent in option
     * @param int $agentId
     * @return void
     */
    public static function saveAgentIdOption(int $agentId): void
    {
        Option::set(\Dev2funImageCompress::MODULE_ID, self::OPTION_NAME_AGENT, $agentId);
    }

    /**
     * @return array|false|null
     */
    public static function getAgent()
    {
        static $agent = null;
        if ($agent === null) {
            $rs = CAgent::GetList([], ['NAME' => LazyConvert::class . '::agentRun();']);
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
            } else {
                self::saveAgentIdOption($agentId);
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

    /**
     * Проверка, что агенты переведены на крон
     * @return bool
     */
    public static function agentsUseCrontab(): bool
    {
        return \Bitrix\Main\Config\Option::get("main", 'agents_use_crontab', 'N') === 'N';
    }

    /**
     * Проверка, что включена работа агентов
     * @return bool
     */
    public static function checkAgents(): bool
    {
        return \Bitrix\Main\Config\Option::get("main", 'check_agents', 'Y') === 'N';
    }
}