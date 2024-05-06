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

run_folder='/var/www/html/DeepGPCR_BC/run_folder'

python  $run_folder/read_smi_protein_nnn_usage.py  $name  'mp_data_'$base  > $base'.log'

for name in  data1/processed/L_P_train_*.pt
do

base=${name:16 }
base_n=${base%.pt}
echo $base
python  $run_folder/training_nn3_load_name.py  $base_n > $base_n'_predict.log'  


done

bash $run_folder/score_sort.bash





cd ..

#######################docking part have finished#######################
rm -rf data_all
mkdir data_all

cp -r   all_out*    DeepBindBC_output


zip -q -r  $prot_ligands'_result.zip'  DeepBindBC_output



