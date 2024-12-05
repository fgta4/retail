#!/bin/bash

# options:
# -n: (name) nama longtask
# -p: (pid) kode longtask
# -u: (username) user_id pemroses
# -data: (data) data parameter yang di proses

# contoh eksekusi
# ./tfi-tbsyncrv.sh -n process-tbsyncrv -p 123456780 -u 5effbb0a0f7d1 -i RV/05/WH-JKT/2300000001


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
command="php $clipath retail/merch/merchorderout/importregtopo $opt"

echo "\$ $command"
$command
echo