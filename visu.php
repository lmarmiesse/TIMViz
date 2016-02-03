<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="favicon.ico"/>
<link href="css/style.css" rel="stylesheet" />
<meta charset=utf-8 />
<title>Metabolic visualisation</title>
<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="js/cytoscape.min.js"></script>

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

	if (!array_key_exists('categ1Name',$_POST)){
		echo "Error, go to <a href='index.php'>index page</a>";
		exit();
	}


	$cond1 = $_POST["categ1Name"];
	$cond2 = $_POST["categ2Name"];
	
	//~ To know which cy.js file to load
	$uniqueNumber = $_POST["uniqueNumber"];
	$uniqueNumberAnalysis = $_POST["uniqueNumberAnalysis"];
	
	 if (!is_dir("analyses/".$uniqueNumber)){
		 
	
		echo "Error. You have been inactive for too long, please relaunch your analysis.";
		exit();
		 
	 }


?>

<script>
var uniqueNumber = <?php echo $uniqueNumber; ?>;
var uniqueNumberAnalysis = <?php echo $uniqueNumberAnalysis; ?>;
</script>

<script src="analyses/<?php echo $uniqueNumber; ?>/analyses/<?php echo $uniqueNumberAnalysis?>/cy.js"></script>
<script src="js/cy2.js"></script>

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
	<div class="row">

		<div class="hero-unit">
			<h1>Pathway visualisation</h1>

			<p>Here is the visualisation page. 

<br/>Start by selecting a pathway you want to see in the drop-down list below and click the "Add" button. To remove a pathway from the visualisation, select it and click the "Remove" button.

To help you in your choice, we provide you with:
			<ul>
				<li>The list of most differentially expressed patways (bottom left of the page).</li>
				<li>The list of pathways directly connected to the ones that are displayed (bottom righ of the page).</li>
			</ul>
			You can display as many pathways as you like.
			</p>
			<p>Reactions are represented as squares and colored according to their score. The legend is shown on the left. You can get more information about reactions by simply clicking them.</p>
		</div>
		<h1 class="text-center">  <?php echo "$cond1  VS $cond2"?> </h1>
		<div id="options">

			<h3>Select pathways to display :</h3>
			<select id="pathwayChoice" class="form-control"></select>

			<div style="margin-top:10px;margin-bottom:10px;">
				<button class="btn btn-success" type="submit" onclick="addPathway();">Add</button>
				<button class="btn btn-danger" type="submit" onclick="removePathway();">Remove</button>
			</div>

		</div>
	</div>
	
	<div class="row" >
		<div class="text-center" id="legend" style="" >
			<h2>Legend</h2>
			<hr>
			<h5 style = "text-align:center;"><?php echo "$cond1  > $cond2"?></h5>
			<div style="background: linear-gradient(to bottom, rgba(255,0,0,1) 0%,rgba(239,239,239,1) 50%,rgba(0,0,255,1) 100%);height: 150px;width: 50px;margin-left: auto; margin-right: auto;"></div>
			<h5 style = "text-align:center;"><?php echo "$cond2  > $cond1"?></h5>

			<hr>
			
			<div style="background-color:white;border:1px dashed; ;height: 35px;width: 35px;margin-left: auto; margin-right: auto;"></div>
			<h5 style = "text-align:center;">Reaction without a gene association</h5>
			
			<hr>
			<br/>
			<button class="btn btn-primary text-center" type="submit" onclick="pngExport();" >Export as png</button>
			<button class="btn btn-primary text-center" type="submit" onclick="scoresExport();" style="margin-top:20px">Export scores</button>


		</div>
		<div id="cyTotal">
			
			<div id="cy"></div>
			<div class="text-center" style="margin-top:10px;">
			
			<label class="checkbox-inline checkbox-success"><input type="checkbox" onclick="updateshowMetabs(this);" ><font size="4" face="verdana">Show metabolites</font></label>
			<label class="checkbox-inline checkbox-success"><input id = "showMetabsBox" type="checkbox" onclick="updateshowNames(this);" checked><font size="4" face="verdana">Show names</font></label>
			
			</div>
		</div>
		
		<div id="reactionDiv">
		<!--
			<h1>Reaction panel</h1>
		-->
	
			<h4 id="reactionDivInfo">Click on a reaction for more information.</h4>
			<h2 id="reactionDivID"></h2>
			<p id="reactionDivName"></p>
			<hr>
			<p id="reactionDivPathways"></p>

	

			<!--
			LISTS
			-->
			<ul id="toggle-view">
				<li class="bar">
					<h3>Reactants</h3>
					<span>-</span>
					<div class="panel">
						<p>
							<ul id='reactantsList'>
						
							</ul>
						</p>
					</div>
					
				</li>
				<li class="bar">
					<h3>Products</h3>
					<span>-</span>
					<div class="panel">
						<p>
							<ul id='productsList'>
						
							</ul>
						</p>
					</div>
				</li>
				<li class="bar">
					<h3>Associated genes</h3>
					<span>-</span>
					<div class="panel">
						<p>
							<ul id='gprList'>
						
							</ul>
						</p>
					</div>
				</li>
			</ul>

		</div>

		

	</div>

<!--
	<div class="row" style="background-color:#d7f4e3;">
-->
	<div class="row" >
		<br/>
		<div style="margin-left: 0.5%;">
			<h1 style="display: inline">Additional information </h1> 
			<h4 style="display: inline"> (Click to expand)</h4> 
		</div>
	</div>
		
	<div class="row" style="">

		<div class="info" id="diffExprPathwaysDiv" >

			<ul id="toggle-view">
				<li class="bar">

					<h4>Most differentially expressed pathways: </h4>
					<span>+</span>
						<div class="panel" style="display:none;">
							<ul id="diffExprPathwaysList">

							</ul> 
						</div>

				</li>
			</ul>
		</div>


		<div class="info" id="pathwaysDiv" >
			
			<ul id="toggle-view">
				<li class="bar">

					<h4>Pathways that are displayed: </h4>
					<span>+</span>


					<div class="panel" style="display:none;">
						<p  id="displayedPathways"></p>

						<ul id="pathwaysList">
							<h4 style="color:red;">There are no selected pathways. Please select a pathway to display</h4>
						</ul> 
						
					</div>

				</li>
			</ul>


		</div>

		<div class="info" id="touchingPathwaysDiv" >
			
			<ul id="toggle-view">
				<li class="bar">

					<h4>Pathways connceted to the ones displayed </h4>
					<span>+</span>

					<div class="panel" style="display:none;">
						<ul id="touchingPathwaysList">

						</ul> 
					</div>

				</li>
			</ul>

		</div>


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
