<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }
?>
<script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js'></script> 
<?php
global $USER;
if (!$USER->IsAuthorized()) {
    echo "Для отправки документа требуется авторизация!";
} else { // если пользователь авторизован, тогда показываем форму 
?>
    <form action="" method="POST" enctype="multipart/form-data">
<?php 
    $str="<h1>Добавить документ</h1>";
    $str.="Телефон: <input type='text' name='phone' placeholder='Укажите телефон' /><br>";
    $str.="Дата рождения: <input type='text' name='bday' placeholder='Выберите дату рождения' /><br>";
    $str.="Имя: ";
    // если у пользователя не заполнено имя, тогда добавим поле для ввода имени
    if (!empty($USER->GetFirstName())) { $str.=$USER->GetFirstName()."<br>"); }
    else { $str.="<input type='text' name='name' placeholder='Укажите имя' /><br>"; }
    echo $str;
    echo CFile::InputFile("doc", 20, ""); 
?>
    <input type="submit" name="save" value="Сохранить">
    </form>
<?php    
}
?>
<script type="text/javascript">
$(document).click(function() {
    $('.err').remove();
});
$('form').submit(function() {
    // чистим ошибки
    $('.err').remove();
    // проверяем поля формы
    if ($(this).find('input[name=name]').val() == '') {
        $('form').find('input[name=name]').before('<div class="err">Укажите имя!</div>');
        $("body,html").animate({scrollLeft: 0, scrollTop:$('.err').offset().top-100}, 100);
        return false;
    } 
    var reg1 = /^[+][0-9] [(][0-9]{3}[)] [0-9]{3}[-][0-9]{2}[-][0-9]{2}$/;
    var reg2 = /[0-9]{11}/;
    if (reg1.test($(this).find('input[name=phone]').val()) || reg2.test($(this).find('input[name=phone]').val())) { 
        $('form').before('<div class="err">Неверный формат телефона! Допустимый формат телефона: +9 (999) 999-99-99 или 89999999999</div>');
        $("body,html").animate({scrollLeft: 0, scrollTop:$('.err').offset().top-100}, 100);
        return false;
    }
    
    if (/^[0-9]{2}[\.][0-9]{2}[\.][0-9]{4}$/.test($(this).find('input[name=bday]').val())) { 
        $('form').before('<div class="err">Неверный формат даты! Допустимый формат даты: ДД.ММ.ГГГГ</div>');
        $("body,html").animate({scrollLeft: 0, scrollTop:$('.err').offset().top-100}, 100);
        return false;
    }
    
    var doc = $(this).find('input[name=doc]').val();
    if (doc == '') {
        $('form').before('<div class="err">Выберите файл!</div>');
        $("body,html").animate({scrollLeft: 0, scrollTop:$('.err').offset().top-100}, 100);
        return false;
    } else {
        if (doc.substr(-3) != "doc" || doc.substr(-3) != "pdf") { 
            $('form').before('<div class="err">Файлы имеют недопустимое расширение. Выберите pdf ил doc файл.</div>');
            $("body,html").animate({scrollLeft: 0, scrollTop:$('.err').offset().top-100}, 100);
            return false;
        }    
    }
});
</script>
