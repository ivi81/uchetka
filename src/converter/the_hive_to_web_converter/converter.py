#!/usr/local/bin/python3.6
# -*- coding:utf-8 -*- 

import argparse
import datetime
from inspect import getsourcefile
from os.path import abspath 
from os.path import join 
from os.path import basename
from os.path import dirname

from the_hive_data_converter._config import load_config
from the_hive_data_converter._ftp import FTPConnector
from the_hive_data_converter._db_connector import Data_on_KA
from the_hive_data_converter._case_json_parser import json_fields_filter
from the_hive_data_converter import _logger as log


current_dir_path_= dirname(abspath(getsourcefile(lambda:0)))

config_path=join(current_dir_path_,"the_hive_data_converter/config.ini")
conf=load_config(config_path)

log_error_path=join(current_dir_path_,conf['log']['error'])
error_log=log.log_error_open(conf['log']['error'],__name__)


if __name__ == "__main__":
    db_conn=Data_on_KA(conf['mysql'],error_log, conf['log']['cases'])
    ftp=FTPConnector(conf['ftp'],error_log)

    ftp.set_RETR_callback(json_fields_filter , db_conn.case_processing)
    ftp.processing_remote_files()
    
   # import json
   # with open("/home/ivi/MY_PROJ/python/the_hive_json_to_uchetcka_converter/json_data/1604675713_2020_11_06_18_15_13_-Z61nHUB2Uh4lTjhgY_0.case.json") as f:
    #    test=json_fields_filter(f.read())
    #    db_conn.case_processing(test)
