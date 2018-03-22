<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

if (!empty($_POST['save'])) { // если нажата кнопка Сохранить
    if (CModule::IncludeModule('iblock')) { // если модуль инфоблока загружен, тогда продолжим, иначе вернём ошибку
        $iblock_ID = $arParams["IBLOCK_ID"];
            
        // т.к. на стороне клиента может быть отключены скрипты, то сделаем проверку формы ещё раз
        $arr_post = array(
                'phone'    => NULL,
                'bday'    => NULL,                        
                'name'    => NULL
            );
        $arr_posted = array();
        $errors = array();
                
        foreach ( $arr_post as $key => $data ) 
        {
            if ( $_POST[$key] ) { // Проверка на наличие элемента $key в массиве $_POST            
                $arr_posted[$key] = $_POST[$key]; // если элемент существует, то присвоем его значение массиву
            } else { // иначе ошибка    
                $errors[$key] = true;
            }
        }
        if (empty($errors)) { // если поля заполнены, проверим их на валидность
            $pattern1 = "/^[+][0-9] [(][0-9]{3}[)] [0-9]{3}[-][0-9]{2}[-][0-9]{2}$/";
            $pattern2 = "/[0-9]{11}/";
            if (!preg_match($pattern1, $arr_posted['phone']) || !preg_match($pattern2, $arr_posted['phone'])) {
                echo "Неверный формат телефона! Допустимый формат телефона: +9 (999) 999-99-99 или 89999999999";
                $errors['error'] = true;
            }
            if (!preg_match("/^[0-9]{2}[\.][0-9]{2}[\.][0-9]{4}$/", $arr_posted['bday'])) {
                echo "Неверный формат даты! Допустимый формат даты: ДД.ММ.ГГГГ";
                $errors['error'] = true;
            }
            if (empty($errors)) { // если ошибок пока нет
                $filename = $_FILES['doc']['name'];
                if (empty($filename)) { echo "Выберите файл!"; }
                else { // если файл выбран, то проверим его раширение
                    $arr_file=$_FILES['doc'];
                    $checkfile = CFile::CheckFile($arr_file,0,false,'pdf,doc'); 
                    if (strlen($checkfile) > 0) { echo "Файлы имеют недопустимое расширение. Выберите pdf ил doc файл.<br>"; }
                    else { // если с расширением всё в порядке, то сохраним файл                    
                        $fid = CFile::SaveFile($arr_file, "docs");
                        
                        $el = new CIBlockElement;
                        $PROP = array(); // добавим массив свойств для элемента, где пропишем данные файла и пользователя
                        $PROP['FILE_DATA'] = $arr_file;  
                        $PROP['FILE_ID'] = $fid;
                        $PROP['USER_DATA'] = $arr_posted;
                        $arr_load_file = Array(  
                           'CREATED_BY' => $USER->GetID(),
                           'DATE_CREATE' => date("d.m.Y H:i:s"),
                           'IBLOCK_SECTION_ID' => false,   
                           'IBLOCK_ID' => $iblock_ID,
                           'PROPERTY_VALUES' => $PROP,  
                           'NAME' => 'Документ создан пользователем: '.$USER->GetID().', Время создания: '.date("d.m.Y H:i:s"),  
                           'ACTIVE' => 'Y'
                        );
                        
                        if ($elem_ID = $el->Add($arr_load_file)) {
                           echo 'Файл загружен, данные записаны!';
                        } else {
                           echo 'Ошибка - что-то пошло не так: '.$el->LAST_ERROR;
                        }                            
                                        
                    }                
                }
            }
            
        } else {
            echo "Не все поля заполнены!";
        }    
        $this->IncludeComponentTemplate();    
    }
    else { echo "Не загружен модуль!"; }

}
