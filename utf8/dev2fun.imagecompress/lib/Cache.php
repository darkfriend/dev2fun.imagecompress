<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.9.0
 * @since 0.9.0
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Config\Option;
use CAgent;
use Guzzle\Tests\Mock\ErrorResponseMock;

IncludeModuleLangFile(__FILE__);

class Cache
{
    const OPTION_NAME_DELETE_AGENT = 'cache_delete_agent';
    const OPTION_NAME_DELETE_LENGTH = 'cache_delete_length';

    /**
     * Return current cache engine
     * @return string
     */
    public static function getCacheEngine()
    {
        return \Bitrix\Main\Data\Cache::getCacheEngineType();
    }

    /**
     * Run agent
     * @return string
     */
    public static function agentRun()
    {
        if (self::getCacheEngine() === 'cacheenginefiles') {
            $limit = \Bitrix\Main\Config\Option::get('dev2fun.imagecompress', self::OPTION_NAME_DELETE_LENGTH, 1000);
            if (empty($limit)) {
                $limit = 1000;
            }
            \Bitrix\Main\Data\CacheEngineFiles::delayedDelete($limit);
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
        $startTime = ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 120, 'FULL');
        $agentId = CAgent::AddAgent(
            self::class . '::agentRun();',
            \Dev2funImageCompress::MODULE_ID,
            'Y',
            120,
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

        Option::set(\Dev2funImageCompress::MODULE_ID, self::OPTION_NAME_DELETE_AGENT, $agentId);

        return $agentId;
    }

    /**
     * Return agent id from bitrix options
     * @return int|null
     */
    public static function getAgentIdOption(): ?int
    {
        return Option::get(\Dev2funImageCompress::MODULE_ID, self::OPTION_NAME_DELETE_AGENT) ?: null;
    }

    /**
     * @return array|false|null
     */
    public static function getAgent()
    {
        static $agent = null;;
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
        $agentId = self::getAgentId();
        return (bool)CAgent::Update($agentId, ['ACTIVE' => 'Y']);
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function deactivateAgent(): bool
    {
        $agentId = self::getAgentId();
        return (bool)CAgent::Update($agentId, ['ACTIVE' => 'N']);
    }

    /**
     * @return string
     */
    public static function getAgentActiveValue(): string
    {
        return self::getAgent()['ACTIVE'] ?? 'N';
    }
}