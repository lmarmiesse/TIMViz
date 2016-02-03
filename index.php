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

<?php


?>





</head>
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

	<div class="hero-unit">
			
			<p>
				Welcome to <b>TIM-Viz</b>.
			</p>
			<p>
				TIM-Viz is a free tool that allows to explore metabolic networks thanks to transcriptomic data. It is based on interactive visualisation of
				 metabolic pathways that might be differentially used by the cell in different conditions.
				 To see visuslisation examples, see <a href="screenshots.html">screenshots</a>.
			</p>
			<p>
				You can get started right now ! You need two input files:
				<ul>
					<li>A text file describing transcriptomic data results. It needs to be tab delimited, genes must be in lines. See an <a href="examples/transEx.txt">example</a>.</li>

					<li>A file describing the metabolic network in the <a href="http://sbml.org/Main_Page">SBML</a> format. It needs to contain pathway and gene association information (click <a href="sbmlDetails.html">here</a> for more details).</li>
				</ul>
			</p>

			<p>
				If you want to try TIM-Viz with example files, you can download:
				<ul>
					<li>An example transcriptomic data file of <i>Arabidopsis Thaliana</i> gene IDs and 10 samples : <a href="examples/AraTransEx.txt" download>Transcriptomic data</a>.</li>

					<li>A reconstructed metabolic network of <i>Arabidopsis Thaliana</i> (de Oliveira Dal'Molin <i>et al.</i> 2010) : <a href="examples/AraGEM.xml" download>AraGEM</a>.</li>
				</ul>
			</p>


		</div>


	<div class="row">

		<form action="upload.php" method="post" enctype="multipart/form-data">
			<div class="col-md-4">
			 
		 	</div>
			<div class="col-md-8">
				<h4>Transcriptomic data file</h4>
			
				<input name="ufile[]" id="ufile[]" type="file" class="file"/>

				</br>
				<h4>SBML file (metabolic network)</h4>
				<input name="ufile[]" id="ufile[]" type="file" />
				</br>
				<input class="btn btn-success" type="submit" value="Start TIM-Viz !" />
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

</html>
