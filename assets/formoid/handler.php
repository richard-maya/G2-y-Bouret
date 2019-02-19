<?php
define('EMAIL_FOR_REPORTS', 'pedro.bouret@g2ybouret.com.mx');
define('RECAPTCHA_PRIVATE_KEY', '@privatekey@');
define('FINISH_URI', 'https://g2ybouret.com.mx/confirmacion.html');
define('FINISH_ACTION', 'redirect');
define('_DIR_', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('PROJECT_FILE', _DIR_ . 'form.formoid');
include _DIR_ . 'helpers.php';

function frmd_ready(){
    if ('redirect' == FINISH_ACTION) exit(header('Location: ' . FINISH_URI));
    exit(header('Location: ' . frmd_action() . '?success=true'));
}

function frmd_error($msg = '', $field = ''){
    static $error = array();
    if ($num = func_num_args()){
        if (1 == $num) exit($msg);
        $error = func_get_args();
        return;
    }
    return $error;
}

function frmd_action($only_path = false){
    $url = 'http://' . $_SERVER['HTTP_HOST'] .
        preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
    if ($only_path) $url = preg_replace('/\/[^\/]+$/', '/', $url);
    return $url;
}

function frmd_captcha_is_valid(&$request){
    require_once _DIR_ . 'recaptchalib.php';
    foreach (array(
            'recaptcha_challenge_field',
            'recaptcha_response_field'
        ) as $key){
        if (!isset($request[$key]))
            $request[$key] = '';
    }
    $resp = recaptcha_check_answer(
        RECAPTCHA_PRIVATE_KEY,
        $_SERVER['REMOTE_ADDR'],
        $request['recaptcha_challenge_field'],
        $request['recaptcha_response_field']
    );
    return $resp -> is_valid;
}

function frmd_mail($report, $subject = ''){
    if (!defined('EMAIL_FOR_REPORTS') || !EMAIL_FOR_REPORTS) return false;
    if (!$subject){
            $subject = 'Nuevo mensaje recibido en ' . $_SERVER['HTTP_HOST'];
    }
    $charset = defined('PAGE_ENCODING') ? PAGE_ENCODING : 'UTF-8';
    if (defined('EMAIL_SENDER') && EMAIL_SENDER) $sender = EMAIL_SENDER;
    else $sender = 'leads@' . $_SERVER['HTTP_HOST'];
    $headers  = "From: " . $sender . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=" . $charset . "\r\n";
    $headers .="Content-Transfer-Encoding: 8bit";
    return mail(EMAIL_FOR_REPORTS, "=?" . $charset . "?B?" . base64_encode($subject) . '?=', $report, $headers);
}

function frmd_pre_save($elm = array(), $value = '', $original = ''){
    static $data = array();
    if (!func_num_args()) return $data;
    $data[ $elm['name'] ] = array(
        'title' => frmd_label($elm),
        'type'  => $elm['type'],
        'value' => $original ? $original : $value
    );
}

function frmd_csv_row($row, $comma = ',', $quote = '"', $end = "\n"){
    $csv = '';
    for ($i = 0, $count = count($row); $i < $count; $i++){
        $csv .= $quote . str_replace($quote, $quote . $quote, $row[$i]) . $quote;
        $csv .= $i < ($count - 1) ? $comma : $end;
    }
    return $csv;
}

function frmd_save(){
    static $assoc = array(
        'first'   => 'First Name',
        'last'    => 'Last Name',
        'addr1'   => 'Street',
        'addr2'   => 'Extended',
        'city'    => 'City',
        'state'   => 'Region',
        'zip'     => 'Postal Code',
        'country' => 'Country'
    );
    $row = $titles = array();
    $fields = frmd_pre_save();
    foreach ($fields as $name=>$field){
        switch ($field['type']){
            case 'name':
            case 'address':
                foreach ($field['value'] as $k=>$v){
                    $titles[] = isset($assoc[$k]) ? $assoc[$k] : $k;
                    $row[] = $v;
                }
                break;
            default:
                $titles[] = $field['title'];
                if (is_array($field['value']))
                    $row[] = implode(', ', $field['value']);
                else $row[] = $field['value'];
                break;
        }
    }
    if (defined('FILE_FOR_REPORTS')){
        if (false === FILE_FOR_REPORTS) return false;
        $file = FILE_FOR_REPORTS;
    } else {
        $path = './reports/';
        if (!is_dir($path) && !@mkdir($path))
            return frmd_error('Cannot create folder "' . $path . '"');
        if (!is_file($path . '.htaccess')){
            $status = @file_put_contents($path . '.htaccess', '# Don\'t show directory listings for URLs which map to a directory.
Options -Indexes
# Protect files and directories from prying eyes.
<FilesMatch "\.csv$">
    Order allow,deny
</FilesMatch>');
            if (!$status) return frmd_error('Cannot create file "' . $path . '.htaccess"');
        }
        $file = $path . 'formoid.csv';
    }
    $exists = file_exists($file);
    if ($fh = @fopen($file, 'a')){
        if (!$exists) fputs($fh, frmd_csv_row($titles));
        fputs($fh, frmd_csv_row($row));
        fclose($fh);
    } else return frmd_error('Cannot create file "' . $file . '"');
    return true;
}

function frmd_label(&$elm){
    $label = '';
    if (isset($elm['label'])) $label = trim(strip_tags($elm['label']));
    if (!$label) $label = $elm['name'];
    return $label;
}

function frmd_handler(){

    ob_start();
    register_shutdown_function(create_function('', '
        echo str_replace(
            "{{Formoid}}",
            frmd_end_form(),
            @ob_get_clean()
        );
    '));

    $request = &$_POST;
    if (0 == count($request)) return;
    
    if (!file_exists(PROJECT_FILE)) return frmd_error('Project file not found.');
    $project = json_decode(file_get_contents(PROJECT_FILE), true);
    
    $report = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html xmlns=http://www.w3.org/1999/xhtml> <head> <meta http-equiv=Content-Type content="text/html; charset=UTF-8" /> <meta name=viewport content="width=device-width, initial-scale=1"> <meta http-equiv=X-UA-Compatible content="IE=edge" /> <title></title> <style type=text/css>#outlook a{padding:0}.ReadMsgBody{width:100%}.ExternalClass{width:100%}.ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div{line-height:100%}body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}table,td{mso-table-lspace:0;mso-table-rspace:0}img{-ms-interpolation-mode:bicubic}</style> </head> <body bgcolor=#e5e4e1> <table width=100% border=0 cellspacing=0 cellpadding=0> <tr> <td> <table width=804 border=0 cellspacing=0 cellpadding=2 align=center bgcolor=#e3e3e3> <tr> <td> <table width=800 border=0 cellspacing=0 cellpadding=10 bgcolor=#ffffff> <tr> <td width=294></td> <td width=212> <a href=https://iconica.mx title="Iconica Studio"> <img src=https://iconica.mx/mails/iconica-logo.png alt=iconica-studio-logo width=100% style=padding-top:20px> </a> </td> <td width=294></td> </tr> <tr> <td width=294></td> <td width=212> <div style=height:2px;background-color:#faaf41 width=100%></div> </td> <td width=294></td> </tr> </table> </td> </tr> <tr> <td> <table width=800 border=0 cellspacing=0 cellpadding=0 align=center bgcolor=#ffffff> <tr> <td> <table width=800 border=0 cellspacing=0 cellpadding=30 align=center bgcolor=#ffffff> <tr> <td> <table width=740 border=0 cellspacing=0 cellpadding=0 align=center bgcolor=#ffffff style=font-family:Arial,Helvetica,sans-serif;font-size:14pt;line-height:140%;color:#25272e> <tr> <td width=740 valign=top>';
    $report .= '<h2>Hola,</h2> <p>Un usuario llenó el formulario de contacto en g2ybouret.com.mx y dejó la siguiente información:</p> <br>';

    foreach ($project['elements'] as $elm){
        if (isset($elm['type']) && 'recaptcha' === $elm['type']){
            if (!frmd_captcha_is_valid($request))
                return frmd_error('The reCAPTCHA wasn\'t entered correctly. Go back and try it again.', 'captcha');
            continue;
        } else if (!isset($elm['required'], $elm['name'], $elm['type'])
            || !$elm['name']) continue;
        $value = $original = '';
        $supported = true;
        if (isset($request[ $elm['name'] ]))
            $value = $request[ $elm['name'] ];
        if ($supported){
            frmd_pre_save($elm, $value, $original);
            $report .= '<p><strong>' . frmd_label($elm) . ': </strong>';
            if (is_array($value)) $report .= implode(', ', $value);
            else if ('rating' == $elm['type'] && isset($elm['stars']) && is_int($value))
                $report .= sprintf('%d of %d', $value, $elm['stars']);
            else $report .= $value;
            $report .= "</p>";
        }
    }

    $report .= '<br></td></tr><tr><td><p><small> <strong>NOTA: </strong>Éste mensaje es generado automáticamente, favor de no responderlo; su función es únicamente informativa.<br>- Gracias </small> </p> </td> </tr> </table> </td> </tr> </table> <table width=800 border=0 cellspacing=0 cellpadding=30 align=center bgcolor=#f1f1f1 style=font-family:Arial,Helvetica,sans-serif;font-size:10pt;line-height:130%;color:#a1a1a1> <tr> <td> <table width=740 border=0 cellspacing=0 cellpadding=0 bgcolor=#f1f1f1> <tr> <td> <table width=740 border=0 cellspacing=0 cellpadding=0> <tr> <td width=400> <span style=font-family:Arial,Helvetica,sans-serif;color:#a1a1a1;font-size:10px>';
    $report .= 'Estás recibiendo éste correo porque se registró un nuevo usuario en el formulario de contacto en g2ybouret.com.mx <br> <a href="mailto:leads@iconica.mx?subject=Desuscribirme&body=Formulario de Contacto" style=color:gray>Dejar de recibir éste correo</a> | <a href=https://iconica.mx/aviso-privacidad.html/ style=color:gray>Aviso de Privacidad</a> </span> <br /><br /> <span style=font-family:Arial,Helvetica,sans-serif;color:#a1a1a1;font-size:10px> ICONICA STUDIO | <a href=mailto:contacto@iconica.mx style=color:gray>contacto@iconica.mx</a> </span> <br /><br /> </td> <td width=140 valign=top> <a href=https://www.facebook.com/IconicaMx/><img border=0 src=https://iconica.mx/mails/facebook.png></a> <a href=https://instagram.com/iconicamx><img border=0 src=https://iconica.mx/mails/instagram.png></a> <a href=https://www.pinterest.com.mx/iconicamx><img border=0 src=https://iconica.mx/mails/pinterest.png></a> <a href=https://behance.net/iconicamx><img border=0 src=https://iconica.mx/mails/behance.png></a> <a href=https://www.linkedin.com/company/iconica-studio><img border=0 src=https://iconica.mx/mails/linkedin.png></a> </td> </tr> </table> </td> </tr> </table> </td> </tr> </table> </td> </tr> </table> </td> </tr> </table> </td> </tr> </table> </body> </html>';
    
    frmd_save();
    frmd_mail($report);
    frmd_ready();
    
}

frmd_handler(); ?>
