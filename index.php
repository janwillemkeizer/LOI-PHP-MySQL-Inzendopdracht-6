<?php
// Load Database settings
include 'config/connection.php';

// Load settings
include 'config/settings.php';

// Load Error_handler
include 'error_handling/error_handler.php';

set_error_handler("error_handler");

$message = '';

$reset = filter_input(INPUT_POST, 'reset', FILTER_SANITIZE_STRING);
$submit = filter_input(INPUT_POST, 'submit', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$voornaam = filter_input(INPUT_POST, 'voornaam', FILTER_SANITIZE_STRING);
$tussenvoegsel = mysqli_real_escape_string($connection,
        filter_input(INPUT_POST, 'tussenvoegsel', FILTER_SANITIZE_STRING));
$achternaam = filter_input(INPUT_POST, 'achternaam', FILTER_SANITIZE_STRING);
$straat = filter_input(INPUT_POST, 'straat', FILTER_SANITIZE_STRING);
$huisnummer = filter_input(INPUT_POST, 'huisnummer', FILTER_SANITIZE_STRING);
$postcode = filter_input(INPUT_POST, 'postcode', FILTER_SANITIZE_STRING);
$woonplaats = filter_input(INPUT_POST, 'woonplaats', FILTER_SANITIZE_STRING);
$geboortedatum = filter_input(INPUT_POST, 'geboortedatum', 
        FILTER_SANITIZE_STRING);
$form_geboortedatum = filter_input(INPUT_POST, 'geboortedatum', 
        FILTER_SANITIZE_STRING);
$man = filter_input(INPUT_POST, 'man', FILTER_SANITIZE_STRING);
$vrouw = filter_input(INPUT_POST, 'vrouw', FILTER_SANITIZE_STRING);
$ingang = filter_input(INPUT_POST, 'ingang', FILTER_SANITIZE_STRING);
$einde = filter_input(INPUT_POST, 'einde', FILTER_SANITIZE_STRING);
$form_einde = filter_input(INPUT_POST, 'einde', FILTER_SANITIZE_STRING);
$sportonderdeel = filter_input(INPUT_POST, 'sportonderdeel', 
            FILTER_SANITIZE_STRING); 
$lesdag = filter_input(INPUT_POST, 'lesdag', FILTER_SANITIZE_STRING);
$geslacht = null;

// Clear form
if(isset($reset)) {
    unset($voornaam, $tussenvoegsel, $achternaam, $straat, $huisnummer, 
            $postcode, $woonplaats, $form_geboortedatum, $man, $vrouw, $ingang,
            $form_einde, $sportonderdeel, $lesdag, $submit);
    $_POST = array();
          $message = 'Formulier is leeg. Je kan opnieuw beginnen. ';
}

// Check for correct email 
function emailcheck($email) {
    if (preg_match('#[a-zA-Z0-9]{2,}@[a-zA-Z0-9]{2,}\.nl#', $email))
  {
    return TRUE;
  } else {
    return FALSE;
  }  
}

if (isset($submit))
{
// Process emailadres    
if (emailcheck($email) || empty($email)) {

if (isset($man)) {
$geslacht = $man;    
} elseif (isset ($vrouw)) {     
$geslacht = $vrouw;
}

// Prepare date fields to insert into database in right format
if(empty($geboortedatum)){
    $geboortedatum = "NULL";
} else {
    $geboortedatum = "'$geboortedatum'";
}
if(empty($einde)){
    $einde = "NULL";
} else {
    $einde = "'$einde'";
}

if(!empty($voornaam) && !empty($achternaam) && !empty($ingang) 
        && !empty($sportonderdeel)) {

$sql_leden = "INSERT INTO leden (Voornaam, Tussenvoegsels, Achternaam, Straat,
    Huisnummer, Postcode, Woonplaats, Email, Geboortedatum, Geslacht)
    VALUES ('$voornaam','$tussenvoegsel', '$achternaam', '$straat',"
        . "'$huisnummer','$postcode','$woonplaats','$email',$geboortedatum,"
        . "'$geslacht')";

    mysqli_query($connection, $sql_leden) or die (mysqli_error($connection));
    
// query database for ID last inserted member
$sql_lid_id = "SELECT MAX(ID)
  FROM Leden";

$lid_id_result = mysqli_query($connection, $sql_lid_id) 
        or die (mysqli_error($connection));

$lid_id = mysqli_fetch_array($lid_id_result, MYSQLI_NUM);
 
$sql_lidmaatschap = "INSERT INTO lidmaatschap (LedenID, Datumingang, Datumeinde, 
    Sportonderdeel, Lesdag)
    VALUES ('{$lid_id[0]}','$ingang',$einde, '$sportonderdeel', '$lesdag')";

    mysqli_query($connection, $sql_lidmaatschap) 
            or die (mysqli_error($connection));
    
    mysqli_close($connection);
     
    // send email to new member and to member administration
            
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        
        $message_to_member = "
        <html>
        <head>
        </head>
        <body>
        <h1>Aanmelding gelukt!</h1>
        <p>Beste $voornaam,</p>
        <p>Je aanmelding is gelukt bij de Omnisportveniging!</p>
        <p>Van harte welkom en we hopen dat je het naar je zin gaat hebben.</p>
        <p>Met vriendelijke groet,</p>
        <p>Leo de Voorzitter.</p>
        </body>
        </html>
        ";
        
        if (!isset($lid_id)) {
            $lid_id = null;
        }
        
        $message_to_administration = "
        <html>
        <head>
        </head>
        <body>
        <h1>Nieuwe aanmelding bij onze Omnisportvereniging</h1>
        <p>Beste ledenadministratie,</p>
        <p>Er heeft zich een nieuw lid aangemeld!</p>
        <p>Dit zijn de gegevens:</p>
        <p>Naam: $voornaam $tussenvoegsel $achternaam</p>
        <p>Lidnummer: $lid_id[0]</p>
        <p>Sportonderdeel: $sportonderdeel</p>
        <p>Dit bericht is afkomstig vanaf de website.</p>
        </body>
        </html>
        ";
        
        $message = 'Bedankt, de aanmelding is geslaagd!';
        
      
        $email_aanmelder = mail($email,'Jouw aanmelding bij de '
                . 'Omnisportvereniging',
                $message_to_member, $headers);


        // See settings.php!
        $email_administratie = mail($email_ledenadministratie,
                'Nieuw lid Omnisportvereniging',
                $message_to_administration, $headers);
            
        if (!$email_aanmelder || !$email_administratie) {
          $message = 'Aanmelden is gelukt, '
                  . 'maar het versturen van de beide e-mails niet (helemaal).';
           }
     
        } else {
            $message = 'Niet alle verplichte velden zijn ingevuld.';
        }
} else {
    $message= 'Vul alsjeblieft een correct e-mailadres in dat eindigt op .nl';
}
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Inzendopdracht 051R6</title>
        <style type='text/css'>
            h1 {
                color: mediumvioletred;
            }
            h3 {
                color: dodgerblue;
            }
        </style>
    </head>
    <body>
        <h1>Aanmelden bij de Omnisport Vereniging</h1>
        <h3>De leukste vereniging.</h3>
        <img src="images/sports.jpg" width="500px">
        <h2><?php echo $message; ?></h2>
        <form method="post" action="index.php">

            Voornaam<font color="red">*</font>: <input type="text" size="20" 
            name="voornaam" value="<?php if(isset($voornaam)) 
            echo $voornaam; ?>" > <br>

            Tussenvoegsel: <input type="text" size="15" name="tussenvoegsel"
            value="<?php if(isset($tussenvoegsel)) echo $tussenvoegsel; ?>"><br>
            
            Achternaam<font color="red">*</font>: <input type="text" size="30" 
            name="achternaam" value="<?php if(isset($achternaam)) 
            echo $achternaam; ?>"> <br>
            
            Straat: <input type="text" size="50" name="straat"
            value="<?php if(isset($straat)) echo $straat; ?>"> <br>
            
            Huisnummer: <input type="text" size="10" name="huisnummer"
            value="<?php if(isset($huisnummer)) echo $huisnummer; ?>"> <br>
            
            Postcode: <input type="text" size="6" name="postcode"
            value="<?php if(isset($postcode)) echo $postcode; ?>"> <br>
            
            Woonplaats: <input type="text" size="30" name="woonplaats"
            value="<?php if(isset($woonplaats)) echo $woonplaats; ?>"> <br>
            
            E-mail: <input type="email" size="50" name="email"
            value="<?php if(isset($email)) echo $email; ?>"> <br>
            
            Geboortedatum: <input type="date" size="15" name="geboortedatum"
            value="<?php if(isset($geboortedatum)) echo $form_geboortedatum; 
            ?>"> <br>
            
            Geslacht: 
            <br>
            Man: <input type="radio" name="man" value="M"><br>
            Vrouw: <input type="radio" name="vrouw" value="V"><br><br>
            
            Datum ingang lidmaatschap<font color="red">*</font>: 
            <input type="date" size="15" 
            name="ingang" value="<?php if(isset($ingang)) echo $ingang; ?>"> 
            <br>
            
            Datum einde lidmaatschap (indien van toepassing): <input type="date"
            size="15" name="einde" value="<?php if(isset($einde)) 
            echo $form_einde; ?>"> <br>
            
            Sportonderdeel<font color="red">*</font>: 
            <select name="sportonderdeel">
            <option disabled <?php if(empty($sportonderdeel)) 
                echo 'selected value'; ?>> -- selecteer een sportonderdeel -- 
            </option>
            
            <option value="Tennis"<?php if (isset($sportonderdeel)) 
            if ($sportonderdeel === 'Tennis') echo 'selected value'; ?>>
            Tennis</option>
            
            <option value="Voetbal"<?php if (isset($sportonderdeel)) 
            if($sportonderdeel === 'Voetbal') echo 'selected value'; ?>>
            Voetbal</option>
            
            <option value="Tafeltennis" <?php if (isset($sportonderdeel)) 
            if($sportonderdeel === 'Tafeltennis') echo 'selected value'; ?>>
            Tafeltennis</option>
            
            <option value="Biljart" <?php if (isset($sportonderdeel)) 
            if($sportonderdeel === 'Biljart') echo 'selected value'; ?>>
            Biljart</option>
            </select><br>
            
            Lesdag: <select name="lesdag">
            <option disabled <?php if(empty($sportonderdeel)) 
                echo 'selected value'; ?>> -- selecteer een lesdag -- 
            </option>
            <option value="Maandag"<?php if (isset($lesdag)) 
            if ($lesdag === 'Maandag') echo 'selected value'; ?>>Maandag</option>
            <option value="Dinsdag"<?php if (isset($lesdag)) 
            if ($lesdag === 'Dinsdag') echo 'selected value'; ?>>Dinsdag</option>
            <option value="Woensdag"<?php if (isset($lesdag)) 
            if ($lesdag === 'Woensdag') echo 'selected value'; ?>>Woensdag</option>
            <option value="Donderdag"<?php if (isset($lesdag)) 
            if ($lesdag === 'Donderdag') echo 'selected value'; ?>>Donderdag</option>
            <option value="Vrijdag"<?php if (isset($lesdag)) 
            if ($lesdag === 'Vrijdag') echo 'selected value'; ?>>Vrijdag</option>

            </select><br><br>
            
            <input type="submit" name="reset" value="Reset">
            <input type="submit" name="submit" value="Verzend">
            
        </form>
    </body>
</html>
