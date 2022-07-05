
kielce -q index.html.erb > index.html
kielce -q publications.html.erb > publications.html
kielce -q software.html.erb > software.html

# kielce -q JLSCircuitTester/index.html.erb > staging/JLSCircuitTester/index.html
# kielce -q NiftyAssignments/index.html.erb > staging/NiftyAssignments/index.html

# scp -r Images staging
# scp -r Software staging
# scp -r Zawilinski staging
# (cd ../WebPages; rsync -rRlzp General ../CISWebPage/staging/Teaching/Courses/)
