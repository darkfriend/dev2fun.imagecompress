<?php
/**
 * Created by PhpStorm.
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.4.0
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Config\Option;

class Process
{
    /**
     * @param array $params = [
     *     'sort' => [],
     *     'filters' => [
     *          'contentType' => [],
     *          'compressed' => 'N',
     *      ],
     *      'limit' => 30,
     * ]
     * @return array[]|bool
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function start($params = [])
    {
        if(!Compress::getEnable()) return false;
        $params['filters'] = array_merge(
            [
                'contentType' => [
                    'image/jpeg',
                    'image/png',
                    'application/pdf',
                ],
                'compressed' => 'N'
            ],
            isset($params['filters']) ? $params['filters'] : []
        );

        if(empty($params['limit'])) {
            $params['limit'] = Option::get('dev2fun.imagecompress', "cnt_step", 30);
        }
        if(!isset($params['sort'])) {
            $params['sort'] = [];
        }

        $compress = Compress::getInstance();
        $rsRes = static::getQuery([
            'sort' => $params['sort'],
            'filters' => [
                '@CONTENT_TYPE' => $params['filters']['contentType'],
                'COMRESSED' => $params['filters']['compressed'],
            ]
        ]);
//        $rsRes = $compress->getFileList(
//            [],
//            [
//                '@CONTENT_TYPE' => $params['filters']['contentType'],
//                'COMRESSED' => $params['filters']['compressed'],
//            ]
//        );
        $rsRes->NavStart($params['limit'], false);

        $result = [
            'error' => [],
            'success' => [],
        ];
        $stepOnPage = 1;
        while ($arFile = $rsRes->NavNext(true)) {
            $strFilePath = \CFile::GetPath($arFile["ID"]);
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $strFilePath)) {
                $compress->addCompressTable($arFile['ID'], [
                    'FILE_ID' => $arFile['ID'],
                    'SIZE_BEFORE' => 0,
                    'SIZE_AFTER' => 0,
                ]);
                $result['error'][] = $arFile['ID'];
            } else {
                $compress->compressImageByID($arFile['ID']);
                $result['success'][] = $arFile['ID'];
            }
            $stepOnPage++;
        }

        $result['updFiles'] = $stepOnPage;
        return $result;
    }

    /**
     * @param array $params = [
     *     'sort' => [],
     *     'filters' => [],
     * ]
     * @return bool|\CDBResult
     */
    public static function getQuery($params = array())
    {
        if(!isset($params['sort'])) {
            $params['sort'] = array();
        }
        if(!isset($params['filters'])) {
            $params['filters'] = array();
        }
        return Compress::getInstance()->getFileList(
            $params['sort'],
            $params['filters']
        );
    }
}