from libsbml import *
import re
import math

sys.argv[1]

SBMLpath = sys.argv[2]

def is_number(s):
    try:
        float(s)
        return True
    except ValueError:
        return False

transcriptoFile = sys.argv[1]

f = open(transcriptoFile,"r")

colnames = f.readline().split("\t")

if colnames==["\n"]:
        print "Error in transcriptomic data file: first line is empty, it should contain sample names."
        sys.exit()

if len(colnames)<2:
        print "Error in transcriptomic data file: there has to be at least two samples. Check that your file is tab delimited."
        sys.exit()
        
geneNames = []

lineNumber = 2
for line in f:
        #print line
        
        line = line.replace("\n","")
        if line!="":
                line = line.split("\t")

                geneNames.append(line[0])

                if len(line[1:]) != len(colnames):
                    print "Error in transcriptomic data file line "+str(lineNumber)+", "+str(len(line[1:]))+" values for "+str(len(colnames))+" column names. Check that your file is tab delimited."
                    sys.exit()
                    
                
                for expr in line[1:]:
                    if not is_number(expr):
                        print "Error in transcriptomic data file line "+str(lineNumber)+", \""+expr+"\" is not a number."
                        sys.exit()
                    else:
                        if math.isnan(float(expr)):
                            print "Error in transcriptomic data file line "+str(lineNumber)+", \""+expr+"\" is not a number."
                            sys.exit()
                        else:
                            if float(expr)<0:
                                print "Error in transcriptomic data file line "+str(lineNumber)+", expression values must be positive."
                                sys.exit()
                                

        lineNumber += 1
	

f.close()



reader = SBMLReader()
document = reader.readSBML(SBMLpath)
model = document.getModel()

if model==None:
        print "Error : the SBML file cannot be loaded. Check that it is a valid SBML file."
        sys.exit()


GPRgenes = []

GPRreacs = []

hasGPR=False

for r in model.getListOfReactions():
	
	
	m = re.search('p>GENE_ASSOCIATION:(.+)</', r.getNotes().toXMLString())
	if m:
            found = m.group(1)
            GPRs = found.replace("(","").replace(")","").replace(" ","").replace("and","or").split("or")
            hasGPR = True
		
            for GPR in GPRs:
                if GPR != "":
                    if not GPR in GPRgenes:
				    #Only the ones in the transcriptomics analysis
                        if GPR in geneNames:
                            GPRreacs.append(r.getId())
                            GPRgenes.append(GPR)

                            
nbReacHasPathways=0
for r in model.getListOfReactions():
	m2 = re.search('p>SUBSYSTEM:(.+)</', r.getNotes().toXMLString())
	if m2:
            nbReacHasPathways+=1
        
				
				
if (len(GPRgenes)>0 and nbReacHasPathways>0):
        print "ok"
        print str(len(GPRgenes))
        print " ".join(colnames).replace("\n","")
        print str(len(set(GPRreacs)))+" reactions out of the "+str(len(model.getListOfReactions()))+" in the metabolic network have associated genes present in the transcriptomic data."
        print str(nbReacHasPathways)+" reactions out of the "+str(len(model.getListOfReactions()))+" in the metabolic network have an associated pathway in the SBML file."
	
else:
    if hasGPR:
        print "Error, the row names of the transcriptomic data do not correspond to the gene-reaction associations in the SBML file"
    else:  
        print 'No GPR association was found in the SBML file. Check that it is weel formated. see <a href="sbmlDetails.html">here</a>.'
    if nbReacHasPathways==0:
        print 'No pathways association was found in the SBML file. Check that it is weel formated. see <a href="sbmlDetails.html">here</a>.'
	





