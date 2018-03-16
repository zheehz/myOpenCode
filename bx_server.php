<?php 

if(!empty($_POST['save'])) // если нажата кнопка Сохранить
{
    if (CModule::IncludeModule('iblock')) { // если модуль инфоблока загружен, тогда продолжим, иначе вернём ошибку
        $ibtype = "savedDocsByUser";
        $ibname = "Документы";
		
        function addIblockType($ibt){
			// проверка на наличие типа инфоблока
			$type = CIBlockType::GetByID($ibt);
			if($type->Fetch()){ //если тип инфоблока уже создан, то можем создать сам инфоблок
				return true;
			} else { // если тип инфоблока не существует, то создадим его
				$obj_ib_type =  new CIBlockType;
				$arr_ib_type_fields = Array(
				  "ID"=>$ibt,
				  "SECTIONS"=>"Y",
				  "IN_RSS"=>"N",
				  "SORT"=>"100",
				  "LANG"=>Array(
					 "ru"=>Array(
						"NAME"=>"Инфоблок для хранения документов",               
					 )   
				  )
				);
				$res = $obj_ib_type->Add($arr_ib_type_fields);
				if(!$res){ // если тип инфоблока создать не удалось, выводим сообщение об ошибке, в ином случае можем создать сам инфоблок
				  echo 'Ошибка: '.$obj_ib_type->LAST_ERROR.'<br>';
				  return false; 
				} else { return true; }	
			}	   
		}
		 
		function addIblock($ibt,$ibn){
			// проверка на наличие инфоблока
			$ib_type_name = CIBlock::GetList(Array(), Array('TYPE' => $ibt, 'NAME' => $ibn));
			if ($res_ib_type_name=$ib_type_name->Fetch()){ // если инфоблока уже существует, то можем создать его элементы
				$res_ID=$res_ib_type_name['ID'];
				return $res_ID;
			} else {
				$obj_ib = new CIBlock;
				$arr_ib_fields = Array(
					"NAME"=> $ibn,
					"ACTIVE" => "Y",
					"IBLOCK_TYPE_ID" => $ibt,
					"SITE_ID" => "s1"
				);
				$new_ib_ID = $obj_ib->Add($arr_ib_fields);
				if(!$new_ib_ID){ // если инфоблок создать не удалось, выводим сообщение об ошибке, в ином случае можем создать его элементы
				  echo 'Ошибка: '.$obj_ib->LAST_ERROR.'<br>';
				  return false; 
				} else { return $new_ib_ID; }	
			}       
		}
			
		// т.к. на стороне клиента может быть отключены скрипты, то сделаем проверку формы ещё раз
		$arr_post = array(
				'phone'	=> NULL,
				'bday'	=> NULL,						
				'name'	=> NULL
			);
		$arr_posted = array();
		$errors = array();
				
		foreach ( $arr_post as $key => $data ) 
		{
			if ( $_POST[$key] ) // Проверка на наличие элемента $key в массиве $_POST
			{
				$arr_posted[$key] = $_POST[$key]; // если элемент существует, то присвоем его значение массиву
			} else {	// иначе ошибка	
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
					if(strlen($checkfile) > 0) { echo "Файлы имеют недопустимое расширение. Выберите pdf ил doc файл.<br>"; }
					else { // если с расширением всё в порядке, то сохраним файл					
						$fid = CFile::SaveFile($arr_file, "docs");
						
						if ($is_type = addIblockType($ibtype)) { // если тип инфоблока существует, тогда можно создать инфоблок, если он ещё не создан
							if ($iblock_ID = addIblock($ibtype,$ibname)) { // если инфоблок существует, тогда можно добавить элементы
							
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
								
								if($elem_ID = $el->Add($arr_load_file)) {
								   echo 'Файл загружен, данные записаны!';
								} else {
								   echo 'Ошибка - что-то пошло не так: '.$el->LAST_ERROR;
								}
							
							}
						}
													
					}				
				}
			}
			
		} else {
			echo "Не все поля заполнены!";
		}	
		
	}
	else { echo "Не загружен модуль!"; }

}
