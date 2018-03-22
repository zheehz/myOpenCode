<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

// Подключаем модуль информационных блоков
if (!CModule::IncludeModule("iblock")) { return; }

// Получаем список типов инфоблоков
$arIBlockType = array();
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch()) {
    $arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["~NAME"];
}

// Получаем список инфоблоков
$arIBlock = array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("ACTIVE"=>"Y"));
while ($arr=$rsIBlock->Fetch()) {
    $arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

// Массив описаний параметов компонента
$arComponentParameters = array(
    "GROUPS" => Array(
        "SOURCE" => array(
                "NAME" => "Параметры инфоблока"
            ),
        ),
    "PARAMETERS" => array(
        "IBLOCK_TYPE" => array(
            "PARENT" => "SOURCE",
            "NAME" => "Тип инфоблока",
            "TYPE" => "LIST",
            "VALUES" => $arIBlockType,
            "REFRESH" => "Y",
        ),
        "IBLOCK_ID" => array(
            "PARENT" => "SOURCE",
            "NAME" => "Название инфоблока",
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arIBlock,
            "REFRESH" => "Y",
        ),
        "SET_TITLE" => array(),
        "CACHE_TIME" => array(),
    )
);
