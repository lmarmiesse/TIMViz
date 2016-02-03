from libsbml import *
import re



###########
reacToGPR = {}
aracyc = open("aracyc.txt","r")

for ligne in aracyc:
	sp = ligne.split("\t")
	reac = sp[2]
	gene = sp[6]
	
	
	
	if reacToGPR.has_key(reac):
		reacToGPR[reac].append(gene)
	else:
		reacToGPR[reac]=[gene]



#~ print "("+" or ".join(reacToGPR["RXN-7904"])+")"


######################## SBML GENERATION

reader = SBMLReader()
document = reader.readSBML("aracyc_original.xml")
model = document.getModel()
for r in model.getListOfReactions():
	
	if r.setNotes(r.getNotes().toXMLString().replace("||",";").replace("&lt;","-").replace("&gt;","-").replace("&apos;","").replace("&amp;","").replace("&beta;","beta").replace("&gamma;","gamma").replace("&omega;","omega").replace("&delta;","delta").replace("beta;","beta").replace("gamma;","gamma").replace("delta;","delta").replace("alpha;","alpha").replace("omega;","omega"))!=0:
		print r.getNotes().toXMLString()
	
	m = re.search('<p>GENE_ASSOCIATION:(.+)</p>', r.getNotes().toXMLString())
	if m:
		found = m.group(1)
		rID = r.getId().replace("__45__","-")
		
		if reacToGPR.has_key(rID):
			
			replacment = " ("+" or ".join(reacToGPR[rID])+")"
			r.setNotes(r.getNotes().toXMLString().replace(found,replacment))
			
		if reacToGPR.has_key(r.getName()):
			
			replacment = " ("+" or ".join(reacToGPR[r.getName()])+")"
			r.setNotes(r.getNotes().toXMLString().replace(found,replacment))
			
			
			
	r.setId(r.getId().replace("__45__","_").replace("__46__","_"))
	r.unsetKineticLaw()
	
	
	while len(r.getListOfModifiers())>0:
		modifier = r.removeModifier(0)
		#~ print modifier
		
		model.removeSpecies(modifier.getSpecies())

	#~ print r.getId().replace("__45__","-").replace("__46__",".")
	
	
	
	#~ print len(r.getListOfModifiers())
	
	
for s in model.getListOfSpecies():
	s.unsetNotes()
	s.unsetAnnotation()
	s.unsetMetaId()	
###############""		

writeSBML(document, "aracyc_modif.xml");
		
