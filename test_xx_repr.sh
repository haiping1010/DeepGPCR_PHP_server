export AMBERHOME=/home/zhanghaiping/program/amber16
export PATH=/home/zhanghaiping/anaconda2/bin/:$PATH
conda init bash
source /var/www/.bashrc


prot_ligands=$1


path=$2

echo $path
echo $prot


#################generate pocket###########
cd $path

unzip     $prot_ligands'.zip'
#######

run_folder='/var/www/html/DeepBindBC/run_folder'

mkdir ligands
cp -r $run_folder/../representive_sdf/ZIN*.mol2   ligands/
cp -r $run_folder/conf.txt .
bash $run_folder/run_nn.bash
bash $run_folder/score.bash
mkdir receptor_n
cp -r receptor/*  receptor_n/

cd receptor_n

#cp -r $run_folder/test1.leapin

/home/zhanghaiping/program/amber16/bin/tleap -f   $run_folder/test1.leapin
/home/zhanghaiping/program/gromacs/bin/gmx_mpi pdb2gmx  -f  receptor_n.pdb  -o receptor_processed.pdb -water spce -ignh -ff amber99sb  -merge all

cd ..

#######################docking part have finished#######################


rm -rf collect2_s
mkdir collect2_s
cd  collect2_s

cp -r  ../Docking/*/*.pdbqt  .


bash $run_folder/run_convert_mol2.bash

bash $run_folder/make_folder1.bash


for name in  *_ligand_n?  *_ligand_n??
   do
cd $name

python   $run_folder/python2_L_col_1000_0.4.py   $name

cd ..
done


cd ../



#########################prepare  final input data ###############

rm -rf data_all
mkdir data_all

cp -r   collect2_s/*/*learn_aa1000_0.4.txt    data_all/




python $run_folder/deep_learn_rob_residual_zhpxxx_n_test_0.2.py 


#####################combined the docking result with the DeepBindBC in a summary file

python $run_folder/compare.py   out_list.csv    all_energies.sort


mkdir  DeepBindBC_output

cp -r  out_list.csv  all_energies.sort   summary_all.txt   receptor/receptor.pdb   collect2_s/*.pdbqt   DeepBindBC_output

zip -q -r  $prot_ligands'_result.zip'  DeepBindBC_output


rm -rf  collect2_s
