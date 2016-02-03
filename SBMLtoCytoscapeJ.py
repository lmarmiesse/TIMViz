from libsbml import *
import re
import numpy
import math
#############################SCORES IMPORT

cond1 = ""
cond2 = ""

scores = {}
scoresPos = []
scoresNeg = []
f = open("scoresGenes2","r")
for l in f:
	
	d = l.split("\t")
	val = float(d[1].replace("\n",""))

	scores[d[0]]=val
	
	if val<1:
		scoresNeg.append(val)
	else:
		scoresPos.append(val)
	
	
f.close()

#for red and blue
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
sbmlName = "AraGEM_Cobra_modif.xml"
#~ sbmlName = "ralsto.sbml"
#~ sbmlName = "coli.xml"
reader = SBMLReader()
document = reader.readSBML(sbmlName)
model = document.getModel()


f = open("cy.js","w")


################ PUT EXT METAB DIFFERNET COLOR
################ POUVOIR FACILEMENT ADD UN PATHWAY QUI TOUCHE EN SELECTIONNANT UNE REAC
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
 

f.write("nodes = [\n");
nodes = []
for sp in model.getListOfSpecies():
	nodes.append( "{  group: 'nodes',data: { id: '"+sp.getId()+"', type : 'metab', color : 'white', shape : 'ellipse', 'borderStyle' : 'solid' , pathways : '' } },\n")


reacToPatways = {}
reacToGPR = {}
allPathways = []
for r in model.getListOfReactions():
	m2 = re.search('p>SUBSYSTEM:(.+)</', r.getNotes().toXMLString())
	if m2:
		pathwaysString = m2.group(1)
		
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
		GPRs = found.replace("(","").replace(")","").replace(" ","").split("or")
		
		
		bestScore = 0
		for GPR in GPRs:
			if GPR != "":
				if GPR in scores.keys():
					if reacToGPR.has_key(r.getId()):
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
		borderStyle = "dotted"
	else:
		color = indToCol[geneToInd[bestGene]]
		
		
	nodes.append( "{ group: 'nodes',data: { id: '"+r.getId()+"', type : 'reac', color : '"+color+"', shape : 'rectangle' , borderStyle : '"+borderStyle+"' ,pathways : '"+pathwaysString+"', reversible : '"+ str(r.getReversible()) +"'} },\n")

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
		edges.append( "{ group: 'edges',data: { id: '"+str(edgeId)+"', weight: 1, source: '"+r1.getSpecies()+"', target: '"+r.getId()+"' , stoichiometry : '"+str(r1.getStoichiometry())+"' } },\n")
		edgeId+=1
		
	for r1 in r.getListOfProducts():
		edges.append( "{ group: 'edges',data: { id: '"+str(edgeId)+"', weight: 1, source: '"+r.getId()+"', target: '"+r1.getSpecies()+"' , stoichiometry : '"+str(r1.getStoichiometry())+"' } },\n")
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


f.write("\n\n");
f.write("""var option = '';
for (i=0;i<pathways.length;i++){
   option += '<option value="'+ pathways[i] + '">' + pathways[i] + '</option>';
}
$('#pathwayChoice').append(option);\n""")
  
f.write("""
}); // on dom ready"""
)

f.close()
