
prot=$1
ligand=$1
path=$2


run_folder='/var/www/html/DeepBindBC/run_folder'




grep  "ATOM\|TER\|END"  $prot'_protein.pdb' > $prot'_w.pdb'

python $run_folder/extract_pocket.py  $prot

mkdir Poc_output
cp -r  $prot'_protein.pdb'  $prot'_ligand.mol2'  $prot'_pocket.pdb'  Poc_output

zip -q -r  $prot'_poc_result.zip'  Poc_output



