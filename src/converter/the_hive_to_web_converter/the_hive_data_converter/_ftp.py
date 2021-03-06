#!/usr.bin/python3
# -*- coding:utf-8 -*-
import os

import ftplib 
import re
import socket
 
class FTPConnector:
    def __init__(self, ftp_cred, err_log):
       ftplib.FTP.maxline=20000
       self._login=ftp_cred["user"]
       self._passw=ftp_cred["passw"]
       self._server=ftp_cred["server"]
       self._port=ftp_cred["port"]
       self._remote_dir=ftp_cred["remote_path"]
       self._err_log=err_log
       self._callback_RETR_list=list() # список обработчиков которые вызываются при обработке загружаемого файла
    
    def reconnect(self):
        self._ftp.quit()
        self._ftp.close()
        delattr(self,"_ftp")
        self.connect()

    def silent_reconnect(self):
        self._ftp.quit()
        self._ftp.close()
        delattr(self,"_ftp")
        try:
            ftp=self._ftp
            self._err_log.warn("""Попытка создания еще одного соединени по FTP с {0} !!!""".format(self._server))
        except AttributeError:
            self._ftp=ftplib.FTP()
            self._ftp.connect(self._server,int(self._port))
            self._ftp.login(self._login,self._passw)
            self._ftp.cwd(self._remote_dir)
            self._ftp.encoding='utf-8'
            self._err_log.info("Переподключение по ftp к {0}".format(self._server))

    def connect(self):
       try:
            ftp=self._ftp
            self._err_log.warn("""Попытка создания еще одного соединени по FTP с {0} !!!""".format(self._server))
       except AttributeError:
            print(f"{self._server}, {self._login}, {self._passw}, {self._remote_dir}")
            self._ftp=ftplib.FTP()
            try:
                self._ftp.connect(self._server,int(self._port))
                self._ftp.login(self._login,self._passw)
                self._ftp.cwd(self._remote_dir)
            except ftplib.error_perm:
               self._err_log.error(f"Не верные учетные данные {self._login} {self._passw}")
               raise  SystemExit(1)
            except socket.gaierror:
                self._err_log.error(f"Не удается установить соединение с {self._server}:{self._port}")
                raise SystemExit(1)
            except ftplib.error_perm:
                self._err_log.error(f"Каталог {self._remote_dir} на {self._server}:{self._port} не существует")
                raise SystemExit(1)
            else:
                self._ftp.encoding='utf-8'
                self._err_log.info("Создано подключение по ftp к {0}".format(self._server))
            
    def set_RETR_callback(self,*args):
        self._callback_RETR_list=args

    def processing_remote_files(self):
        """
        Проверка наличия файлов на ftp 
        и выгрузка их в локальную директорию
        """   
        self.connect()
        response = self._ftp.retrlines("LIST",self._callback_LIST)
        if response.startswith('226'):              # Cписок получен успешно
            self._err_log.info("Получение списка файлов из {0}".format(self._remote_dir))
            try:
                for item in self._remote_files:
                    if(self._ftp.maxline < item[1]): # Проверка максимальной длинны принимаемой строки и размера файла
                        self._ftp.maxline=item[1]+10
                    self.processing_remote_file(item[0])
            except AttributeError:
                self._err_log.info("На ftp нет файлов для обработки !!!")
        else:
            self._err_log.warn("ftp code response:226 Ошибка при чтении удаленной директории {0}".format(self._remote_dir)) 
        self._ftp.quit()


    def processing_remote_file(self,fname):
        """
        Обработка файлa с FTP
        по полученному ранее в '_remote_files' списку 
        """
       # response=self._ftp.retrlines("RETR {0}".format(fname)) 
        try:
             self._err_log.info(f"Обрабатывается: {fname} ")
             response=self._ftp.retrlines(f"RETR {fname}",self._callback_RETR) # RETR - команда загрузки файла по FTP                              
             if(not response.startswith('226')):              # Файл не передан успешно
                self._err_log.warn(f"""Ошибка передачи для {fname}. 
                                      Переданный файл может быть 
                                      неполный или поврежденный""")
        except  KeyError as k_err:
              self._err_log.error(f"Файл: {fname} {k_err.args[0]}")
              self.silent_reconnect()
        except IndexError as i_err:
            self._err_log.error(f"Файл: {fname} {i_err.args[0]}")
            self.silent_reconnect()
        finally:
            self._ftp.delete(fname)
            self._ftp.set_pasv(False) 
            #try:
            
            #except ftplib.error_reply:
            #   pass
            #except ftplib.error_perm:
            #    self._err_log(f"Не хватает прав для удаления {fname} - удалите его руками")
              
      
        
    def _callback_LIST(self,line):
        """
        Обработчик результата команды 'LIST' 
        - команда просмотра содержимого удаленной директории
        """
        line_=line.rsplit(" ") # Переводим строку в список - разбиваем по пробелу
        length=len(line_)
        fname=line_[length-1]
        tmp=fname.split(".")
        #fname_regexp=re.compile('(\d+_){7}.+\.case\.txt')
        
        if(tmp[-1]=="txt" and tmp[-2]=="case"):  # Если имя файла отвечает критерию 
            digit=[x for x in line_ if x.isdigit()]
            fsize=max(digit, key=lambda i: int(i))   # Размер файла - Берем самое большое число чтоб уж точно не прогадать
            l=(fname,int(fsize))

            try:    # Запоминаем название файла и его размер
                self._remote_files.append(l)  
            except AttributeError:
                self._remote_files=list()
                self._remote_files.append(l)


    def _callback_RETR(self,line):
         if(len(self._callback_RETR_list)!=0):
            data=line
            for call in self._callback_RETR_list:

                data=call(data)
     
         else:
            print(line)

            
    def loadFilesToFTP(self, files):
        '''
        Загрузка набора файлов на FTP
        '''

        for path in files:
            with open (path, 'rb') as fp:
                file_name=os.path.split(path)[1]
                self._ftp.storbinary(f"STOR {file_name}",fp,1024)
                print(f"файл - {fp.name} загружен в {self._server}/{self._remote_dir}")

        




