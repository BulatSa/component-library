<?php
$subject = $_POST["form_subject"];
$message = "";

foreach ($_POST as $input_name => $input_val) {
	$input_val = htmlspecialchars(strip_tags(trim($input_val)));
	if ($input_name != "form_subject") {
		if ( !strpos($input_name, '_label')){
			$input_label = $input_name . '_label';
			if (!empty($_POST[$input_label])) {
				$message .= $_POST[$input_label] . ": " . $input_val . "<br>";
			} else {
				$message .= $input_name . ": " . $input_val . "<br>";
			}
		}
	}
}

require_once('phpmailer.php');
use PHPMailer\PHPMailer\PHPMailer;
$mail = new PHPMailer;

try {
	$mail->CharSet = 'utf-8';
	$mail->setFrom('no-reply@mail.ru', 'Имя От Кого');
	$mail->addAddress('bs@penbrain.com');
	$mail->isHTML(true);
	$mail->Subject = $subject;
	$mail->Body = $message;

	//Обрабатываем файлы
	if (isset($_FILES)) { //если есть файлы в форме
		foreach ($_FILES as $file_item) {
			if (!is_array($file_item['error'])) { //проверяем множественное или нет поле файла
				if ($file_item['error'] == UPLOAD_ERR_OK) {
					$mail->addAttachment($file_item['tmp_name'],$file_item['name']);
				}
			} else {
				foreach ($file_item["error"] as $key => $error){
					if ($error == UPLOAD_ERR_OK) {
						$tmp_name = $file_item["tmp_name"][$key];
						$name = $file_item["name"][$key];
						$mail->addAttachment($tmp_name,$name);
					}
				}
			}
		}
	}
	//Обрабатываем файлы

	//возвращаем массив с результатом
	$result = array();
	$result['MAIL_SUBJECT'] = $mail->Subject;
	$result['MAIL_BODY'] = $mail->Body;
	$result['MAIL_FROM'] = $mail->From;
	$result['MAIL_FROM_NAME'] = $mail->FromName;

	$mail->send();
	$result['MAILER_CHECK'] = 'Mail OK';
	$result = json_encode($result);
	echo $result;
} catch (Exception $e) {
	$result = json_encode($mail->ErrorInfo);
	echo 'Mailer Error: ' . $result;
}

?>