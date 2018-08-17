<?php

//echo"<pre>"; print_r($_FILES); echo "</pre>"; exit();

require_once ('functions01.php');
require 'database.php';

//PHP variables from image upload form--------------------------------------
$fileName           = $_FILES['Filename']['name'];
$tempFileName       = $_FILES['Filename']['tmp_name'];
$fileSize           = $_FILES['Filename']['size'];
$fileType           = $_FILES['Filename']['type'];
$fileDescription    = $_POST['Description'];

$fileLocation       = "imgRepo/";
$fileFullPath       = $fileLocation . $fileName;
//---------------------------------------------------------------------------



//Check if file exists in database and folder--------------------------------
if(!$fileName) {
    die("No filename.");
}

if ($_FILES['Filename']['error'] !== UPLOAD_ERR_OK) {
    die("Error: Unable to determine <i>image</i> type of uploaded file");
}

$info = getimagesize($_FILES['Filename']['tmp_name']);
if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG)
    && ($info[2] !== IMAGETYPE_PNG) && ($info[2] !== IMAGETYPE_JPG)) {
    die("Not a gif/jpeg/png");
}

if ($fileExists) {
    echo "File <html><b><i>" . $fileName
        . "</i></b></html> already exists in DB. Please rename file.";
    exit();
}

// exit, if requested file already exists -- in the subdirectory
if(file_exists($fileFullPath)) {
    echo "File <html><b><i>" . $fileName
        . "</i></b></html> already exists in file system, "
        . "but not in database table. Cannot upload.";
    exit();
}

if($fileSize > 2000000) { echo "Error: file exceeds 2MB."; exit(); }
//----------------------------------------------------------------------------



// put the content of the file into a variable, $content----------------------
$fp      = fopen($tempFileName, 'r');
$content = fread($fp, filesize($tempFileName));
$content = addslashes($content);
fclose($fp);
//----------------------------------------------------------------------------

// if all of above is okay, then upload the file
$result = move_uploaded_file($tempFileName, $fileFullPath);

// if upload was successful, then add a record to the SQL database
if ($result) {
    echo "Your file <html><b><i>" . $fileName
        . "</i></b></html> has been successfully uploaded";
    $sql = "INSERT INTO upload02(filename,filetype,filesize,description)"
        . " VALUES ('$fileName','$fileType',$fileSize,"
        . "'$fileDescription')";
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $q = $pdo->prepare($sql);
    $q->execute(array());
// otherwise, report error
} else {
    echo "Upload denied for this file. Verify file size < 2MB. ";
}


// connect to database--------------------------------------------------------
$pdo = Database::connect();
//----------------------------------------------------------------------------



// insert file info and content into table------------------------------------
$sql = "INSERT INTO upload (fileName, fileSize, fileType, description, fileLocation, content) "
    . "VALUES ('$fileName', '$fileSize', '$fileType', '$fileDescription', '$fileFullPath', '$content')";
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$q = $pdo->prepare($sql);
$q->execute(array());
//----------------------------------------------------------------------------



// list all uploads in database-----------------------------------------------
// ORDER BY BINARY filename ASC (sorts case-sensitive, like Linux)
echo '<br><br><a href="index.html">back</a><br><br>All files in database...<br><br>';
$sql = 'SELECT * FROM upload '
    . 'ORDER BY BINARY filename ASC;';

foreach ($pdo->query($sql) as $row) {
    $id = $row['id'];
    $sql = "SELECT * FROM upload where id=$id";
    echo $row['fileName'] . '<br>'
        . '<img width=200 src="data:image/jpeg;base64,'
        . base64_encode( $row['content'] ).'"/>'
        . '<br><br>';
}
echo '<br><br>';
//----------------------------------------------------------------------------



// disconnect-----------------------------------------------------------------
Database::disconnect();
//----------------------------------------------------------------------------