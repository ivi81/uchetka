import configparser as confp
import sys

from inspect import getsourcefile
from os.path import abspath 
from os.path import join 
from os.path import basename
from os.path import dirname

def load_config(path):
    """
    Загружаем конфигурационный файл
    """
    current_dir_path_= dirname(abspath(getsourcefile(lambda:0)))
    print(current_dir_path_)
    path=join(current_dir_path_,path)
    conf=dict()
    dir_path=dirname(path)
    with open(path) as config_file:
        cfg=confp.ConfigParser()
        cfg.read_file(config_file)
        try:
            conf['mysql']=dict()
            conf['mysql']['sever']=cfg.get('mysql','server')
            conf['mysql']['port']=cfg.get('mysql','port')
            conf['mysql']['passw']=cfg.get('mysql','passw')
            conf['mysql']['login']=cfg.get('mysql','login')
            conf['mysql']['db']=cfg.get('mysql','db')

            conf['ftp']=dict()
            conf['ftp']['server']=cfg.get('ftp','server')
            conf['ftp']['port']=cfg.get('ftp','port')
            conf['ftp']['user']=cfg.get('ftp','user')
            conf['ftp']['passw']=cfg.get('ftp','passw')
            conf['ftp']['remote_path']=cfg.get('ftp','remote_path')
            conf['ftp']['local_path']=cfg.get('ftp','local_path')

            conf['log']=dict()
            conf['log']['cases']=join(current_dir_path_,"..",cfg.get('log','cases'))
            conf['log']['error']=join(current_dir_path_,"..",cfg.get('log','error'))

            conf['paths']=dict()
            conf['paths']['not_ptocessed_file']=cfg.get('paths','not_ptocessed_file')
        except  confp.NoOptionError as NoOptErr:
            print("выполнение программы прервано, ошибка в конфигурационном файле: {0}".format(NoOptErr.message))
            sys.exit()
    return(conf)
