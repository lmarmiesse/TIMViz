<?php


//get unique number
	$fp = fopen("uniqueNumber", "r+");

	if (flock($fp, LOCK_EX)) { // acquière un verrou exclusif
		
		$uniqueNumber = intval(fgets($fp));
		
		fseek($fp, 0);
		
		ftruncate($fp, 0);     // effacement du contenu
		fwrite($fp, strval($uniqueNumber+1));
		fflush($fp);            // libère le contenu avant d'enlever le verrou
		flock($fp, LOCK_UN);    // Enlève le verrou
	} else {
		echo "Error on server, try again soon !";
	}

	fclose($fp);
////////////


/////////////////////////////////////   REMOVE OLD ANALYSES
function delTree($dir) { 
   $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
    } 
    return rmdir($dir); 
} 

$ls = scandir("analyses/");
//~ print_r($ls);

$timeBeforeRemove = 18000;

for ($anIndex = 2; $anIndex<count($ls); $anIndex ++){
	if (time() - filemtime("analyses/".$ls[$anIndex])>$timeBeforeRemove){
		delTree("analyses/".$ls[$anIndex]);
	}
}

//~ exit();

/////////////////////////////////////


//////////////// UPLOAD CHECKS

	function return_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) 
		{
			case 'g':
			$val *= 1024;
			case 'm':
			$val *= 1024;
			case 'k':
			$val *= 1024;
		}
		return $val;
	}

	
    //select maximum upload size
    $max_upload = ini_get('upload_max_filesize');
    //select post limit
    $max_post = ini_get('post_max_size');
    
    
    //~ echo  $max_post."<br/>";
    
    if (!array_key_exists('CONTENT_LENGTH',$_SERVER)){
		echo "Error, go to <a href='index.php'>index page</a>";
		exit();
	}
    
    $size = (int) $_SERVER['CONTENT_LENGTH'];
    if ($size>return_bytes($max_post)){
		
		exit("Upload error : files are too large. The upload limit on this server is ".$max_post);
		
		
	}
//////////////// 


////////////upload transcriptomic file
$upload1Ok = 1;


if ($upload1Ok == 0) {
    
} else {
	
	
	$target_dir = "analyses/$uniqueNumber/";
	
	mkdir($target_dir, 0777);
	
	$target_trans = $target_dir . basename($_FILES["ufile"]["name"][0]);	
	
	//~ echo $target_trans;
	
    if (move_uploaded_file($_FILES["ufile"]["tmp_name"][0], $target_trans)) {
		
        //~ echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";




    } else {
		
		$upload1Ok = 0;
		echo "Upload error ";

		//~ echo $_FILES["ufile"]['error'][0];
		
        switch( $_FILES["ufile"]['error'][0] ) {
            case UPLOAD_ERR_OK:
                echo "<br/>";
                break;
            case UPLOAD_ERR_INI_SIZE:
				echo ' - transcriptomic file too large. Upload file limit is '.$max_upload."<br/>";
				break;
            case UPLOAD_ERR_FORM_SIZE:
                echo ' - transcriptomic file too large.'.'<br/>';
                break;
            case UPLOAD_ERR_PARTIAL:
                echo ' - file upload was not completed.'.'<br/>';
                break;
            case UPLOAD_ERR_NO_FILE:
                echo ' - zero-length transcriptomic data file uploaded.'.'<br/>';
                break;
            default:
                echo ' - internal error #'.$_FILES['ufile']['error'][0].'<br/>';
                break;
        }
    }

}


////////////upload SBML file
$upload2Ok = 1;


if ($upload2Ok == 0) {



} else {
	
	$target_sbml = $target_dir . basename($_FILES["ufile"]["name"][1]);	
	
	//~ echo $target_sbml;
	
    if (move_uploaded_file($_FILES["ufile"]["tmp_name"][1], $target_sbml)) {
		
        //~ echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
		
		$upload2Ok = 0;
		echo "Upload error ";
		
        switch( $_FILES["ufile"]['error'][1] ) {
            case UPLOAD_ERR_OK:
                echo "<br/>";
                break;
            case UPLOAD_ERR_INI_SIZE:
				echo " - SBML file too large. Upload file limit is ".$max_upload."<br/>";
				break;
            case UPLOAD_ERR_FORM_SIZE:
                echo ' - SBML file too large.'.'<br/>';
                break;
            case UPLOAD_ERR_PARTIAL:
                echo ' - file upload was not completed.'.'<br/>';
                break;
            case UPLOAD_ERR_NO_FILE:
                echo ' - zero-length SBML file uploaded.'.'<br/>';
                break;
            default:
                echo ' - internal error #'.$_FILES['ufile']['error'][1].'<br/>';
                break;
        }
    }

}


if ($upload2Ok==1 && $upload1Ok==1){
	


	$output = shell_exec("python checkFiles.py $target_trans $target_sbml 2>&1");
    
   
    
    $output = explode("\n",$output);
    
       //echo "<pre>";
       //print_r($output);
       //echo "</pre>";
    
	if ($output[0]=="ok"){
		
		$nbCommun = $output[1];
		$colnames = explode(" ",$output[2]);
		
		$reacGPRmessage =  $output[3];
		
		$reacPathwaymessage =  $output[4];
		
		
		
		//create folder for different analyses
		$analyses_dir = "analyses/$uniqueNumber/analyses";
	
		mkdir($analyses_dir, 0777);
		
		
		?>
	


<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="favicon.ico"/>
<link href="css/style.css" rel="stylesheet" />
<meta charset=utf-8 />
<title>Transcriptome metabolic viewer</title>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>

<link href="css/bootstrap.min.css" rel="stylesheet" />

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries 
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
		
		<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>


<script src="js/upload.js"></script>
<body style="background-color: #f5f5f5;">
     <nav class="navbar-collapse collapse" id="top" role="banner" style="background-color: #d2d2d2; height:100px;">
	  <div class="container">
		  
	   
		  <div class="col-md-3">
			  <a class="" href="index.php"><img src="img/logo_withName.png" alt="Logo"  style="height:80px;margin-top:10px;"></a>
		  </div>
		  <div class="col-md-6">
			   <a class="" href="index.php" style=""><img src="img/name.png" alt="Logo" style="height:95px;margin-top:2px;"></a>
			   
		  </div>
		  <div class="col-md-3"></div>
	  </div>
    </nav>

	<div class="container" style="background-color: #ffffff;">
		
		<div class="row">

			<div style="border: 1px solid;padding: 10px;">
			<h4>Some information about your data:</h4>
			<?php
				
				echo "<p>$nbCommun genes are in commun between the transcriptomic data and the SBML file.</p>";
				echo "<p>$reacGPRmessage</p>";
                echo "<p>$reacPathwaymessage</p>";
				
				echo "<p>If these numbers are low, try to complete the gene annotations in the SBML file.</p>";
			?>
			</div>
		</div>
		<div class="hero-unit">
			<h1>Choice of the two conditions to compare</h1>
			
			<p>
				You now need to choose the two conditions of the transcriptomic data that you want to compare.
			</p>
			<p>
				Select samples from the list on the left. Then, use the center buttons to fill the two lists on the righ corresponding to the two conditions that will be compared. You can put as many sample as you want in each condition.
			</p>
			

		</div>
		<div class="row">
			<form action="sendRequest.php" method="post" target="_blank">
				<div class="col-md-5">
					<?php echo count($colnames)." samples in the transcriptomic data";?>
					<h3>Samples to choose from:</h3>
					
					Filter
					<input type="text" class="form-control" id="filter">
					
					
					<select id="CategSelect" size="25" multiple class="form-control">
						
						<?php
							$i=1;
							foreach ($colnames as $value){
							
								echo "<option value= '$i' > $value</option>\n";
								$i = $i+1;
						
							}
						
						?>
						
						

					</select>
				</div>
				<div class="col-md-2" style="top:100px;">
				
					<button type="button" style="width:100%;height : 75px;;margin-top:35px;" class="btn-success" onclick="addSelected(1);">Add selected to condition 1</button>
					</br>
					<button type="button" style="width:100%;height : 75px;" class="btn-default" onclick="addAll(1);">Add all to condition 1</button>
					</br>
					<button type="button" style="width:100%;height : 75px;margin-top:200px;" class="btn-success" onclick="addSelected(2);">Add selected to condition 2</button>
					</br>
					<button type="button" style="width:100%;height : 75px;" class="btn-default" onclick="addAll(2);">Add all to condition 2</button>
					</br>
					</br>
					</br>
					<button type="submit" style="width:100%;height:75px;top:50px;" class="btn btn-primary" >Run analysis</button>
					<input type="hidden" name="uniqueNumber" value="<?php echo "$uniqueNumber"?>">
					<input type="hidden" name="transFile" value="<?php echo "$target_trans"?>">
					<input type="hidden" name="SBMLFile" value="<?php echo "$target_sbml"?>">
				
				</div>
				<div class="col-md-5">
					</br>
					
					<p style="font-size:24px;">Name of condition 1 (optionnal) : <input class="form-control" type="text" name = "categ1Name" value="Condition 1"></p>
					<select id="Categ1Select" name="Categ1Select[]" style="height:230px;" multiple class="form-control">
					</select>
					<div class="row">
						<div class="col-md-6">
							<button type="button" style="width:100%;height : 40px;" class="btn-default" onclick="removeSelected(1);">Remove selected</button>
						</div>
						<div class="col-md-6">
							<button type="button" style="width:100%;height : 40px;" class="btn-default" onclick="removeAll(1);">Remove all</button>
						</div>
					</div>
				
					<p style="font-size:24px;">Name of condition 2 (optionnal) : <input class="form-control" type="text" value="Condition 2" name = "categ2Name"></p>
					<select id="Categ2Select" name="Categ2Select[]" style="height:230px;" multiple class="form-control">
					</select>
					<div class="row">
						<div class="col-md-6">
							<button type="button" style="width:100%;height : 40px;" class="btn-default" onclick="removeSelected(2);">Remove selected</button>
						</div>
						<div class="col-md-6">
							<button type="button" style="width:100%;height : 40px;" class="btn-default" onclick="removeAll(2);">Remove all</button>
						</div>
					</div>
				</div>
			</form>
			
			
			
		</div>
		<footer class="footer">
			<div class="container">
				<p>
					This project was developed at <a
						href="http://www6.toulouse.inra.fr/lipm_eng/">LIPM</a>
					by Lucas Marmiesse. Copyright &copy; 2015, INRA
				</p>
			</div>
		</footer>
	

	</div>
</body>

<script>
$(document).ready(function(){

    var $this, i, filter,
        $input = $('#filter'),
        $options = $('#CategSelect option');
    

    $input.keyup(function(){
        filter = $(this).val();
        i = 1;
        $options.each(function(){
            $this = $(this);
            $this.removeAttr('selected');
            if ($this.text().toLowerCase().indexOf(filter.toLowerCase()) != -1) {
                $this.show();
                $this.attr("v","true");
                if(i == 1){
                    $this.attr('selected', 'selected');
                }
                i++;
            } else {
                $this.hide();
                $this.attr("v","false");
            }
        });
    });

});
</script>

</html>
		
		
		
<?php
		
		
	}
	else{
		
//		echo "Error in uploaded files";
		echo $output[0];
		
	}
	
}
else{
	//~ echo "Upload error";
	
}




?>



