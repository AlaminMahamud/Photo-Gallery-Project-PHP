<?php

    // In generation, this could be moved to a config file
    $upload_errors = array(
                            UPLOAD_ERR_OK           => "No Errors",
                            UPLOAD_ERR_INI_SIZE     => "larger than upload_max_filesize",
                            UPLOAD_ERR_FROM_SIZE    => "Larger than form MAX_FILE_SIZE",
                            UPLOAD_ERR_PARTIAL      => "Partial Upload",
                            UPLOAD_ERR_NO_FILE      => "No File",
                            UPLOAD_ERR_NO_TMP_DIR   => "No Temporary Directory",
                            UPLOAD_ERR_CANT_WRITE   => "Can't Write To Disk",
                            UPLOAD_ERR_EXTENSION    => "File Upload Stopped by extension"
                          );

    $error   = $_FILES['file_upload']['error'];
    $message = $upload_errors[$error];

    echo "<pre>";
    print_r($_FILES['file_upload']);
    echo "<pre>";
    echo "<hr/>";

?>

<html>
    <head>
        <title>Upload</title>
    </head>
    <body>

    <?php
        
    ?>

    </body>
</html>