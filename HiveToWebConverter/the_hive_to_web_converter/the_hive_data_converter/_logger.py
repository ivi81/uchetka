#!/usr/bin/python3
# -*- coding:utf-8 -*- 

import datetime
import csv
from tempfile import NamedTemporaryFile
import shutil
import logging as lg
import os

def log_id_open(path,name):
    '''
    Функция  создания логера обработанных id
    '''
    _log_format_short="%(message)s"

    file_handler = lg.FileHandler(path)
    file_handler.setLevel(lg.WARNING)
    file_handler.setFormatter(lg.Formatter(_log_format_short))

    stream_handler = lg.StreamHandler()
    stream_handler.setLevel(lg.INFO)
    stream_handler.setFormatter(lg.Formatter(_log_format_short))

    logger = lg.getLogger(name)
    logger.setLevel(lg.INFO)
    logger.addHandler(file_handler)
    logger.addHandler(stream_handler)
    
   
    return(logger)


def log_error_open(path,name):
    '''
    Функция конфигурирования и создания логера ошибок
    '''
    _log_format_long = "%(asctime)s - [%(levelname)s] - %(name)s - (%(filename)s).%(funcName)s(%(lineno)d) - %(message)s"
    _log_format_short="%(message)s"

    file_handler = lg.FileHandler(path)
    file_handler.setLevel(lg.WARNING)
    file_handler.setFormatter(lg.Formatter(_log_format_long))

    stream_handler = lg.StreamHandler()
    stream_handler.setLevel(lg.INFO)
    stream_handler.setFormatter(lg.Formatter(_log_format_short))

    logger = lg.getLogger(name)
    logger.setLevel(lg.INFO)
    logger.addHandler(file_handler)
    logger.addHandler(stream_handler)
    
    return(logger)


def log_processed_case(log_path_,data):
    """
    Логирование информации о вновь обработанных кейсах
    в cvs-файл, нужно для дальнейшего перноса  трафика с thehive в flashlight
    """
    fieldnames=["дата_обработки", "№_карточки", "№_сенсора_и_№_кейса", "файлы_на_FTP"]
    row=dict(zip(fieldnames,data))
    log_path=add_timestamp(log_path_)
    if(not os.path.exists(log_path)):
        opt="w"
    else:
        opt="a"    
    with open(log_path, opt, newline='') as out_file:
        writer = csv.DictWriter(out_file, delimiter='\t', fieldnames=fieldnames)
        if(opt=="w"):
            writer.writeheader()
        writer.writerow(row)
        out_file.close()

def add_timestamp(file_path):
    """
    Добавление временной метки в формате "%Y_%m_%d в назавание файла
    """  
    date = datetime.datetime.today().strftime("%Y_%m_%d")
    path=file_path.split("/")
    l=len(path)
    path[l-1]=f"{date}_{path[l-1]}"
    return("/".join(path))

if __name__ == "__main__":
    """
    Тестирование 
    """
    path = "logs/processed_cases.log"
    path = to_path_add_time_marker(path)
    print(path)


