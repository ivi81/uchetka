#!/usr/bin/python3
# -*- coding:utf-8 -*- 
from sys import exit
import datetime

import pymysql

import sys
import the_hive_data_converter._logger  as lg
class Data_on_KA():
    def __init__(self,db_cred,log_err, case_log_path): 
        self.log_err=log_err    
        self.case_log_path=case_log_path

        try:
            # Подключаемся к базе данных.
            self._db_conn = pymysql.connect(host=db_cred['sever'],
                             user= db_cred['login'],
                             password=db_cred['passw'],                             
                             db=db_cred['db'],
                             charset='utf8mb4',
                             cursorclass=pymysql.cursors.DictCursor)
            log_err.info("Соединение с MySQL установлено")
        except pymysql.err.OperationalError as OpErr:
            self.log_err.error(OpErr)
            self.log_err.error("выполнение программы прервано, ошибка соединения с СУБД MySQL:")
            exit()     

    def _get_max_id(self):
        """
        Получение номера последней карточки инцидена
        """
        sql = "SELECT MAX(id) FROM incident_chief_tables"
        with self._db_conn.cursor() as cursor:                
                cursor.execute(sql)
                max_id=cursor.fetchone()
                return(max_id['MAX(id)'])

    def get_new_id(self):
        """
        Формирование номера новой карточки инцидента
        """
        
        id=self._get_max_id()
        if(id is not None):
            id=id+1
        else:
            id=1
        return(str(id))

    def find_id(self, root_ID):
        """
        Поиск номера карточки инцидента соответствующего 
        rootID в the Hive
        """
        sql="SELECT DISTINCT id FROM processed_cases_table WHERE root_ID = %s"
        with self._db_conn.cursor() as cursor:                
            cursor.execute(sql,root_ID)
            id_=cursor.fetchone()
            if(id_ is not None):
                id_=id_['id']
            return(id_)

    def case_processing(self, case_filtered_fields):
        """
        Вставка либо обновление данных
        """
        id_=self.find_id(case_filtered_fields["root_id"])
        if(id_ is None):
            self.insert(case_filtered_fields)
        else:
            self.update(case_filtered_fields, str(id_))
 
    def update(self,case_filtered_fields,id_):
        """
        Обновляем информацию о событии в БД 
        """
        incident_chief_rows=case_filtered_fields['incident_chief']
        incident_additional_rows=case_filtered_fields['incident_additional']
        incident_number_signature_rows=case_filtered_fields['incident_numbers_signature']
        incident_analyst_rows=case_filtered_fields['incident_analyst']
         
        with self._db_conn.cursor() as cursor:

             #Обновляем данные о карточке в incident_chief_tables - первичная информация об инциденте
             index=incident_chief_rows["columns"].index("ip_src")
             complex_update(cursor, 
                            incident_chief_rows,
                            index,
                            "incident_chief_tables",
                            incident_chief_rows["columns"][index],
                            id_)

             #Обновляем данные о карточке в incident_additional_tables  - дополнительная информация об инциденте 
             (incident_additional_rows["columns"],
             incident_additional_rows["values"])=exlude_fields(incident_additional_rows,"solution","number_mail_in_CIB", "number_mail_in_organization")
             cursor.execute(*simple_update("incident_additional_tables", incident_additional_rows, id_))
             
             #Обновляем данные о карточке в incident_number_signature_tables  - список сработавших сигнатур 
             index=incident_number_signature_rows["columns"].index("sid")
             complex_update(cursor, 
                            incident_number_signature_rows,
                            index,
                            "incident_number_signature_tables",
                            incident_number_signature_rows["columns"][index],
                            id_)
             
             #Обновляем данные о карточке в incident_analyst_tables  - информация аналитика
             cursor.execute(*simple_update("incident_analyst_tables", incident_analyst_rows, id_))

             self._db_conn.commit()
             print(f"Обновлена информация о {incident_additional_rows['values'][4]} в карточке № {id_} ")


    def insert(self,case_filtered_fields):
        """
        Добавляем информацию о событии в БД 
        """
        new_id=self.get_new_id() # получаем новый номер карточки

        chief_rows=case_filtered_fields['incident_chief']
        add_id(chief_rows,new_id)

        additional_rows=case_filtered_fields['incident_additional']
        add_id(additional_rows,new_id)

        number_signature_rows=case_filtered_fields['incident_numbers_signature']
        add_id(number_signature_rows,new_id)

        analyst_rows=case_filtered_fields['incident_analyst']
        add_id(analyst_rows,new_id)
         
        with self._db_conn.cursor() as cursor:

            #Вставляем строку в таблицу incident_chief_tables - первичная информация об инциденте
            cursor.executemany(*insert_str("incident_chief_tables", chief_rows))
             
            #Вставляем строку в таблицу incident_additional_tables  - дополнительная информация об инциденте 
            cursor.execute(*insert_str("incident_additional_tables", additional_rows))

            #Вставляем строки в таблицу incident_number_signature_tables  - список сработавших сигнатур 
            cursor.executemany(*insert_str("incident_number_signature_tables", number_signature_rows))

            #Вставляем строку в таблицу incident_analyst_tables  - информацию аналитика
            cursor.execute(*insert_str("incident_analyst_tables", analyst_rows))
            
            #Вставляем номер карточки и root_ID CASE-а в таблицу processed_cases_table - помечаем кейс как обработанный ранее
            processed_cases_row=dict(columns=("root_ID","id", "process_date"),
                                     values=(case_filtered_fields["root_id"], new_id,case_filtered_fields['processing_data']))
            cursor.execute(*insert_str("processed_cases_table", processed_cases_row))

            self._db_conn.commit()
            log_msg=f"в карточку № {new_id} добавлена информация о {additional_rows['values'][7]}  "
            log_data=list()
            log_data.append(case_filtered_fields['processing_data'])
            log_data.append(new_id)
            log_data.append(additional_rows['values'][7])
            log_data.append(additional_rows['values'][6])
            lg.log_processed_case(self.case_log_path, log_data)
            #print(log_msg)
            #with open(self.case_log_path, "a") as case_log:
            #    case_log.write(f"{case_filtered_fields['processing_data']} {log_msg}\n")
            #case_log.close()


"""
Вспомогательные утилиты
"""

def add_id(data,id_):
    """
    Добавляем id карточки к отфильтрованному документу
    """
    try:
        values=data["values"]
    except  TypeError :
        pass
    if(isinstance(values[0],list)):
         for item in values:
             item.append(id_)
    else:
        values.append(id_)
            
def exlude_fields(data, *exclude_columns):
    """
    Исключение полей для update
    """
    if(exclude_columns is not None):
        exclude_indexes=[data["columns"].index(x) for x in exclude_columns]
        l=len(data["columns"])
        columns=[data["columns"][i] for i in range(l) if(i not in exclude_indexes)]
        l=l-1
        if(isinstance(data["values"][0], list)):
            values=list()
            for item in data["values"]:
                v=[item[i] for i in range(l) if(i not in exclude_indexes)]
                values.append(v)
        else:
            values=[data["values"][i] for i in range(l) if(i not in exclude_indexes)] 
        return(columns,values)    
    
def insert_str(table_name,row):
    """
    Формируем строку insert для вставки строки в заданную таблицу
    """
    columns=", ".join(row["columns"])
    count=len(row["columns"])
    values=str.join(", ",["%s"]*count)
    insert_str=f"INSERT INTO {table_name} ({columns}) VALUES ({values})"
    #print(f"insert into {table_name}")
    return (insert_str, row["values"])

def delete_not_exists(table_name, column, data, index, id_):
    """
    Удаление строк из таблицы table_name 
    для которых нет соответствия в списке value_list
    """
    value_list=[str(x[index]) for x in data["values"]]
    value_list=str.join(", ", value_list)
    delete=f"DELETE FROM {table_name} WHERE id =  %s AND {column} NOT IN ( {value_list} )"
  #  print(f"    delete from {table_name}")
    return (delete,(id_,))
                                                                
def find_not_exists(table_name, column, data, index, id_):
    """
    Оставляем только те строки из списка value_list 
    для которых нет соответствия в таблице table_name
    """
    value_list=[x[index] for x in data["values"]]
    value_list=map(lambda x:f" SELECT {x} as {column} ",value_list)
    value_list=str.join(" UNION ", value_list)

    sql=f" SELECT t1.{column} FROM ( {value_list} ) as t1 WHERE t1.{column} NOT IN (SELECT {column} FROM {table_name} where id = %s) "
   # print (f"   find not exist in {table_name}")
    return(sql,(id_,))        

def simple_update(table_name, data, id_, column=None, index=None):
    """
    Формируем  update для oбновления записей в таблице
    """
    updated_columns=", ".join([x+" = %s" for x in data["columns"] if (x!="id" and x != column)])
    update_str=f"UPDATE {table_name} SET {updated_columns} WHERE id = %s"
    data["values"].append(id_)
    if(column is not None and index is not None):
        update_str=f"{update_str} AND {column} = %s "
        elem=data["values"].pop(index)
        data["values"].append(elem)
   # print (f"   simple update {table_name}")
    return (update_str, data["values"])

def forming_colums_and_values(row):

    """
    Формируем кортеж из 2-х элементов 
    содержащих список столбцов и список их значений
    """

    columns=""
    if(isinstance(row,dict)):
        columns=", ".join(row["columns"])
        count=len(row["columns"])
    return(columns,count)

def complex_update(cur, data, index, table_name, column, id_):
    """
    Обновление информации о карточке в таблице
    содержащей несколько записей о карточке (несколько строк с одним id)
    Имеется ввиду обновление списка значений (ip либо sid) связанных  с карточкой

        cur - объект курсор для выполнеиня запроса
        data - обновляемые данные
        index -  индекс столбца в data для дополнительных условий
        table_name - название таблицы
        column - столбец по которому приме6няется дополнительное условие обновления
        id_ - номер инцидента
    """
    #print(f"complex_update {table_name}:")
    #Удаляем все строки для которых нет обновлений
    delete=delete_not_exists(table_name, column, data, index, id_)  
    cur.execute(*delete)

    #Получаем список данных которые еще не добавлялись в таблицу но связанны с карточкой
    sql=find_not_exists(table_name, column, data, index, id_)     
    cur.execute(*sql)
    row=cur.fetchall()

    if(len(row)!=0):   # Если найдены отсутствующие в таблице строки то нужно их добавить
        not_exist_rows=dict()
        not_exist_rows["columns"]=data["columns"]
        not_exist_rows["values"]=[x for x in data["values"] if (x[0] in row)]
        add_id(not_exist_rows,id_)
        cur.execute(*insert_str(table_name,not_exist_rows))

    for item in data["values"]: # Собсно обновление тех данных которые уже были в карточке
        if (item[index] not in row):
            data_=dict(columns=data["columns"],
                    values=item)
            update=simple_update(table_name, data_, id_, column, index)
            cur.execute(*update)

    