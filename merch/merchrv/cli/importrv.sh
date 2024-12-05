#!/bin/bash

# options:
# -n: (name) nama longtask
# -p: (pid) kode longtask
# -u: (username) user_id pemroses
# -data: (data) data parameter yang di proses

# contoh eksekusi
# ./importrv.sh -c "/var/www/html/fgta4/tfimpor/cli" -n process-syncrv -p 652e2db1f32dd -u 5effbb0a0f7d1 -d "{\"region_id\":\"03400\",\"hemoving_id\":\"RV/05/GEX/WH-JKT/230000030\",\"merchrv_id\":\"RV/GEX/23100004\"}" -l "/mnt/ramdisk/log-20231017-652e2db1f32dd.txt"


clear

input_var_n=""  # name longtask
input_var_p=""  # pid longtask
input_var_u=""  # username longtask
input_var_d=""  # data dokument longtask -> parameter ini bisa diganti2 disesuaikan, misalnya json tanggal, doc id, dll
input_var_l=""  # file log
clipath=""  # cli path

while getopts c:n:p:u:d:l: flag
do
	case "${flag}" in
		c) clipath="${OPTARG}";;
		n) input_var_n="--name ${OPTARG}";;
		p) input_var_p="--pid ${OPTARG}";;
		u) input_var_u="--username ${OPTARG}";;
		d) input_var_d="--data ${OPTARG}";;
		l) input_var_l="--log ${OPTARG}";;
	esac
done

opt="$input_var_n $input_var_p $input_var_u $input_var_d $input_var_l"

# contoh clipath /var/www/html/fgta4/kalista/cli
command="php $clipath retail/merch/merchrv/importrv $opt"



echo "\$ $command"
$command
echo