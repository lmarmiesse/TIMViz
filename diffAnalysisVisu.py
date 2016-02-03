from libsbml import *
import re
import numpy
import math

import sys

sys.argv[1]

SBMLpath = sys.argv[1]
#~ SBMLpath = "AraGEM_Cobra_modif.xml"
transcriptoFile = sys.argv[2]
#~ transcriptoFile = "countsMYBSEQ.txt"

categ1Indices = sys.argv[3].split(",")
#~ categ1Indices = range(1,16,1)
categ2Indices = sys.argv[4].split(",")
#~ categ2Indices = range(61,76,1)

uniqueNumber = sys.argv[5]
#~ uniqueNumber = 62

uniqueNumberAnalysis = len(os.listdir("analyses/"+uniqueNumber+"/analyses/"))+1

resultsFolder = "analyses/"+uniqueNumber+"/analyses/"+str(uniqueNumberAnalysis)

os.mkdir(resultsFolder)


############# load transcriptomic data
f = open(transcriptoFile,"r")

colnames = f.readline().split("\t")

dataCateg1 = {}
Categ1Sum = 0
dataCateg2 = {}
Categ2Sum = 0

for line in f:
	line = line.replace("\n","")
	line = line.split("\t")
	
	array1 = []
	for ind in categ1Indices:
		ind = int(ind)
		Categ1Sum = Categ1Sum + float(line[ind])
		array1.append(float(line[ind]))
		
	dataCateg1[line[0]] = array1
	

	array2 = []
	for ind in categ2Indices:
		ind = int(ind)
		Categ2Sum = Categ2Sum + float(line[ind])
		array2.append(float(line[ind]))
		
	dataCateg2[line[0]] = array2
	

f.close()
############# 



############# load GPR of SBML
reader = SBMLReader()
document = reader.readSBML(SBMLpath)
model = document.getModel()

GPRgenes = []
for r in model.getListOfReactions():
	
	
	m = re.search('p>GENE_ASSOCIATION:(.+)</', r.getNotes().toXMLString())
	if m:
		found = m.group(1)
		GPRs = found.replace("(","").replace(")","").replace(" ","").split("or")
		
		for GPR in GPRs:
			if GPR != "":
				if not GPR in GPRgenes:
					#Only the ones in the transcriptomics analysis
					if GPR in dataCateg1.keys():
						GPRgenes.append(GPR)
				
				

#print str(len(GPRgenes))+" genes in common between GPR and transcriptomic data"
############# 


############### NEED TO DO THE PATHWAY WORK





############### 

############### GENES WORK
scores = {}
scoresPos = []
scoresNeg = []

for gene in GPRgenes:
	
	val1 = numpy.mean(dataCateg1[gene])/(float(Categ1Sum)/(float(len(categ1Indices))*len(dataCateg1)))
	
	
	val2 = numpy.mean(dataCateg2[gene])/(float(Categ2Sum)/(float(len(categ2Indices))*len(dataCateg2)))
	if val1>=val2:
		if val2!=0:
			sc = val1/val2
		else:
			continue
  
	else:
		if val1!=0:
			sc = -val2/val1
		else:
			continue
  
	scores[gene] = sc
  
	if sc<1:
		scoresNeg.append(sc)
	else:
		scoresPos.append(sc)







############### 

############### colors
#for red and blue gradients
nbCateg = 20

maxPos = numpy.mean(scoresPos)+numpy.std(scoresPos)*2
minNeg = numpy.mean(scoresNeg)-numpy.std(scoresNeg)*2

indToVal = {}

maxInc =  (maxPos-1)/(nbCateg-1)
for i in range(nbCateg-1):
	indToVal[i+1]=[1+maxInc*i,1+maxInc*(i+1)]
	
indToVal[nbCateg]= [1+maxInc*(nbCateg-1),max(scoresPos)]


minInc =  (minNeg+1)/(nbCateg-1)
for i in range(nbCateg-1):
	indToVal[-(i+1)]=[-1+minInc*(i+1),-1+minInc*i]
	
		
indToVal[-nbCateg]= [min(scoresNeg),-1+minInc*(nbCateg-1)]


geneToInd = {}


for sc in scores:
	val = scores[sc]
	for ind in indToVal:
		if val>=indToVal[ind][0] and val<=indToVal[ind][1]:
			geneToInd[sc]= ind

indToCol = {}
indToCol[1] = "rgb(255,255,255)"
r=255
g=255
b=255
#whiteToRed
for i in range(nbCateg-1):
	inc = int((float(255)/float(nbCateg-1))*(i+1))
	indToCol[i+2]='rgb('+str(r)+','+str(g-inc)+','+str(b-inc)+')'



indToCol[-1] = "rgb(255,255,255)"
r=255
g=255
b=255
#whiteToRed
for i in range(nbCateg-1):
	inc = int((float(255)/float(nbCateg-1))*(i+1))
	indToCol[-(i+2)]='rgb('+str(r-inc)+','+str(g-inc)+','+str(b)+')'
	#~ print i
	

#############################

#############################SBML IMPORT

f = open(resultsFolder+"/cy.js","w")


################ OU JUSTE UNE LISTE DE PATHWAYS QUI TOUCHENT

f.write("""

var nodes;
var edges;
var patways;



$(function(){ // on dom ready

var cy = cytoscape({
  container: document.getElementById('cy'),
  
  style: cytoscape.stylesheet()
    .selector('node')
      .css({
        'background-color':'data(color)',
        'shape': 'data(shape)',
        'content' : 'data(id)'
      })
    .selector('edge')
      .css({
        'target-arrow-shape': 'triangle',
        'width': 4,
        'line-color': 'black',
        'target-arrow-color': 'black'
      })
    ,
  
  elements: {\n""")

f.write("nodes: [\n")



f.write("],\n")
	
f.write("edges: [\n")
	

f.write("]},\n")


f.write("""layout: {
    name: 'cose',
    directed: true,
    roots: '#a',
    padding: 10
  }
});\n""")
 


####
idToName = {}
####

f.write("nodes = [\n");
nodes = []
for sp in model.getListOfSpecies():
	idToName[sp.getId()]=sp.getName()
	nodes.append( "{  group: 'nodes',data: { id: '"+sp.getId().replace("'","")+"' , type : 'metab', color : 'white', shape : 'ellipse', 'borderStyle' : 'solid' , pathways : '' } },\n")

reacScores={}
reacToPatways = {}
reacToGPR = {}
reacToColor = {}
allPathways = []
for r in model.getListOfReactions():
	pathwaysString=""
	idToName[r.getId()]=r.getName()
	m2 = re.search('p>SUBSYSTEM:(.+)</', r.getNotes().toXMLString())
	if m2:
		pathwaysString = m2.group(1)
		

		pathwaysString = re.sub(r'\([^)]*\)', '', pathwaysString)

		if(pathwaysString.replace(" ","")!=""):
		
			pathways = pathwaysString.split(";")
			
			for p in pathways:
				
				p=p.strip()
				allPathways.append(p)
				if reacToPatways.has_key(r.getId()):
					if not p in reacToPatways[r.getId()]:
						reacToPatways[r.getId()].append(p)
				else:
					reacToPatways[r.getId()]=[p]
	
	#GPRS
	bestGene = ""
	m = re.search('p>GENE_ASSOCIATION:(.+)</', r.getNotes().toXMLString())
	if m:
		found = m.group(1)
		GPRs = found.replace("(","").replace(")","").replace(" ","").replace("and","or").split("or")
		
		
		bestScore = 0
		for GPR in GPRs:
			if GPR != "":
				if GPR in scores.keys():
					if reacToGPR.has_key(r.getId()):
			                        if not GPR in reacToGPR[r.getId()]:
							reacToGPR[r.getId()].append(GPR)
					else:
						reacToGPR[r.getId()] = [GPR]
					
					if math.fabs( scores[GPR])>bestScore:
						bestScore = math.fabs( scores[GPR])
						bestGene = GPR
				
				
	borderStyle = "solid"	
	
	#if there is no GPR	
	if bestGene == "":
		color = "rgb(255,255,255)"
		borderStyle = "dashed"
	else:
		reacScores[r.getId()]=scores[bestGene]
		color = indToCol[geneToInd[bestGene]]
		reacToColor[r.getId()] = color
		
	nodes.append( "{ group: 'nodes',data: { id: '"+r.getId().replace("'","")+"', type : 'reac', color : '"+color+"', shape : 'rectangle' , borderStyle : '"+borderStyle+"' ,pathways : '"+pathwaysString+"', reversible : '"+ str(r.getReversible()) +"'} },\n")

allPathways = list(set(allPathways))


for i in range(len(nodes)):
	
	if (i==len(nodes)-1):
		f.write( nodes[i].replace("},","}"))
	else:
		f.write(nodes[i])
		
f.write("];\n");		
		
#EDGES
edgeId=1
f.write("edges = [\n");
edges = []
for r in model.getListOfReactions():
	for r1 in r.getListOfReactants():
		edges.append( "{ group: 'edges',data: { id: '"+str(edgeId)+"', weight: 1, source: '"+r1.getSpecies()+"', sourceName:'"+idToName[r1.getSpecies()].replace("'","")+"', target: '"+r.getId()+"' , targetName:'"+r.getName().replace("'","")+"', stoichiometry : '"+str(r1.getStoichiometry())+"' } },\n")
		edgeId+=1
		
	for r1 in r.getListOfProducts():
		edges.append( "{ group: 'edges',data: { id: '"+str(edgeId)+"', weight: 1, source: '"+r.getId()+"', sourceName:'"+r.getName().replace("'","")+"' , target: '"+r1.getSpecies()+"' , targetName:'"+idToName[r1.getSpecies()].replace("'","")+"', stoichiometry : '"+str(r1.getStoichiometry())+"' } },\n")
		edgeId+=1


for i in range(len(edges)):
	
	
	if (i==len(edges)-1):
		f.write( edges[i].replace("},","}"))
	else:
		f.write(edges[i])

		
f.write("];\n");



#############PATHWAYS
f.write("pathways = [\n");
allPathways = sorted(allPathways,key=lambda s: s.lower())

for i in range(len(allPathways)):
	
	
	if (i==len(allPathways)-1):
		f.write("'"+allPathways[i]+"'\n")
	else:
		f.write("'"+allPathways[i]+"',\n")

		
f.write("];\n\n");

f.write("reacToPatways = {};\n");
for reac in reacToPatways:
	f.write("reacToPatways[\""+reac+"\"] = [\""+"\",\"".join(reacToPatways[reac])+"\"];\n")


f.write("\n\n");


#############


############# REAC TO GPR
f.write("reacToGPR = {};\n");
for reac in reacToGPR:
	f.write("reacToGPR[\""+reac+"\"] = [\""+"\",\"".join(reacToGPR[reac])+"\"];\n")
#############

############# GENE TO COLOR
f.write("geneToColor = {};\n");
for gene in scores:
	f.write("geneToColor[\""+gene+"\"] = '"+indToCol[geneToInd[gene]]+"';\n")
#############



############# Pathway to color
pathwayToScore = {}

for reac in reacToColor:
	if reacToPatways.has_key(reac):
		for pathway in reacToPatways[reac]:
			#diff = 255 - float(reacToColor[reac].split(",")[1])
			diff = math.fabs(reacScores[reac])

			if pathwayToScore.has_key(pathway):
				pathwayToScore[pathway].append(diff)
			else:
				pathwayToScore[pathway] = [diff]


for p in pathwayToScore:
	#only pathways of more than 3 reactions	
	if len(pathwayToScore[p])>=3:
		pathwayToScore[p]= numpy.mean(pathwayToScore[p])
	else:
		pathwayToScore[p]= 0

#f.write("pathwayToScore = {};\n");
f.write("var diffPathways = Array();\n");
for p in sorted(pathwayToScore.items(), key=lambda x: x[1],reverse=True):
	#f.write("pathwayToScore[\""+p[0]+"\"] = '"+str(p[1])+"';\n")
	f.write("diffPathways.push(\""+p[0]+"\");\n")
############




f.write("\n\n");
f.write("""var option = '';
for (i=0;i<pathways.length;i++){
   option += '<option value="'+ pathways[i] + '">' + pathways[i] + '</option>';
}
$('#pathwayChoice').append(option);


var nbPath=0;
for (p in diffPathways){
   $('#diffExprPathwaysList').append('<li>'+diffPathways[p]+'</li>');
   nbPath++;
   if (nbPath==10){
	break;
   }
}

\n""")
  
f.write("""
}); // on dom ready"""
)

f.close()


#write scores
f2 = open(resultsFolder+"/scores.txt","w")

for gene in scores:
	f2.write(gene+"\t"+str(scores[gene])+"\n")

for reac in reacScores:
	f2.write(reac+"\t"+str(reacScores[reac])+"\n")

for p in sorted(pathwayToScore.items(), key=lambda x: x[1],reverse=True):
	pathway = p[0]
	f2.write(pathway+"\t"+str(pathwayToScore[pathway])+"\n")

f2.close()
#

#output : 
print uniqueNumberAnalysis



