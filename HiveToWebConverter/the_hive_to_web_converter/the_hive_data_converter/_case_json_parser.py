

#!/usr/bin/python3
# -*- coding:utf-8 -*- 

from inspect import getsourcefile
from os.path import abspath 
from os.path import join 
from os.path import basename
from os.path import dirname

import json
import codecs
import datetime
import sys

import ipaddress

import traceback

def say_my_name():
    stack = traceback.extract_stack()
    print('from {0}'.format(stack[-2][2]))


def handler_exception(func):
    """
    Декоратор сигнализации о некоректных полях JSON -документа (CASE-а) 
    либо их отсутствии
    """
    def wrap_test(case, artifact , object_,*args):
        try:
            return(func(case, artifact , object_, *args ))
        except TypeError as type_err:   #Тестирование существования типа
            print(f"{type_err} не верное значение в {func.__name__}")        
        except KeyError as key_err:  #Тестирование существования ключа в JSON объекте
            key_err.args=(f"Кейс № {object_['caseId']} - в json-документе отсутствует поле  {key_err.args[0]}",)
            raise(key_err)
    return(wrap_test)

def json_fields_filter(json_doc):
    """
    Фильтруем поля в CASE и Artifacts
    и формируем словари для полей таблиц БД Data_on_KA
    """
    data=json.loads(json_doc)
        
    case=data["Case"]
    artifact=data["Artifact"]
    object_=case["object"]

    if (not isinstance(artifact,list)):
        raise(KeyError)

    (ic,some_text)=filter_for_incident_chief(case, artifact , object_ )
    ins=filter_for_incident_number_signature(case, artifact , object_ )
    iadd=filter_for_incident_additional(case, artifact , object_, some_text)
    ia=filter_for_incident_analyst(case, artifact , object_ )
    process_d= datetime.datetime.now()

    filtered_fields=dict(incident_chief=ic,
                         incident_additional=iadd,
                         incident_numbers_signature=ins,
                         incident_analyst=ia,
                         root_id=get_rootID(case, artifact , object_ ),
                         processing_data=process_d.strftime('%Y-%m-%d %H:%M:%S'))

    return(filtered_fields)
 
@handler_exception
def filter_for_incident_chief(case, artifact , object_ ):
    """
    Фильтр полей JSON документа в поля таблицы incident_chief_tables
    """

    data=dict(columns=("date_time_incident_start",
                             "date_time_incident_end",
                             "date_time_create",
                             "count_impact", "type_attack",
                             "country", "ip_src", "ip_dst","id"))
    src_ip=list()
    dst_ip=list()
    for item in  artifact:      #Получаем списки адресов источников и адресов назначения
        if (item["dataType"]=="ip" and (item["ioc"] or 
                                        case["details"]["resolutionStatus"]=="FalsePositive")):
            src_ip.append(str_to_ip(item["data"]))
            max_mind=get_from_obj(item,"reports","MaxMind_GeoIP_4_0","taxonomies", defaul=None)
            if(max_mind is not None):
                print(max_mind[0]["value"])
        elif(item["dataType"]=="ip_home"):
            dst_ip.append(item["data"].split(":")[1])
       
    try:
        ip_dst=str_to_ip(dst_ip.pop()) # Берем один из ip-адресов назначения (последний в списке), его и ставим в поле dst_ip в таблице
    except IndexError as i_err:
         i_err.args=(f"Кейс № {object_['caseId']} - в данном кейсе отстутствуют ip-адреса",)
         raise(i_err)

    if(len(dst_ip)!=0):
        str_dst_ip=", ".join(dst_ip)
        str_dst_ip="Воздействие так же направлено на следующие адреса: {0}".format(str_dst_ip)
    else:
        str_dst_ip=None

    values=list()     # Все множество строк - соответствует кол-ву ip-адресов в кейсе

    v=list()          # Тиражируемые по кол-ву ip-адресов значения 
   # (date_start,date_end)=date_start_end(object_['description'])
    first_date=date_convert_(object_['customFields']['first-time']['date'])
    last_date=date_convert_(object_['customFields']['last-time']['date'])
    v.append(first_date)
    v.append(last_date)
    v.append(trim_timestamp(object_["createdAt"]))
    v.append(None)

    type_attack=object_["customFields"]["class-attack"]["string"]
    type_attack=attack_type_code.get(type_attack,0)
    v.append(type_attack)   
    
    v.append(get_from_obj(case,"reports","NCCCI_GeoIP.1_0"))

    for ip_src in src_ip:
            v_=list()
            v_.extend(v)
            v_.append(ip_src)
            v_.append(ip_dst)
            values.append(v_)
    try:
        values[0]
    except IndexError as i_err:
        if not i_err.args: 
           i_err.args=('',)
        msg=f"Кейс № {object_['caseId']} - не назначен ioc для ip  "
        i_err.args = (msg,)
        raise(i_err)

    data["values"]=values

    return (data, str_dst_ip)

@handler_exception
def filter_for_incident_additional(case, artifact , object_, some_text=None):
    """
    Фильтр полей JSON документа в поля таблицы incident_additional_tables
    """
    
    data=dict(columns=("login_name","availability_host","direction_attack","solution",
                         "number_mail_in_CIB","number_mail_in_organization","space_safe",
                         "explanation","id"))

    loggin=object_["createdBy"]
    loggin=loggins_accordance.get(loggin,"the_hive")
    if(isinstance(loggin,dict)):
        loggin=loggin.get("d","the_hive")

    values=list()
    values.append(loggin)
    values.append(1)
    values.append(1)
    values.append(None)
    values.append(get_from_obj(object_,"customFields","inner-letter","string")) # № письма во внутреннюю организацию
    values.append(get_from_obj(object_,"customFields","external-letter","string")) # №  письма во внешнюю организацию

    space_safe=[x["data"] for x in  artifact if x["dataType"]=="url_pcap"] # извлекаем список путей к файлам с трафиком 
    space_safe=str.join(" , ",space_safe)
    values.append(space_safe)

    sensors=[x.split("=")[1] for x in object_["tags"] if(x.find("ATs:sensor=")!=-1 or x.find("Sensor_id=")!=-1)] # получаем список сенсоров с которыми связано данное событие
    sensors="сенсор № ".join(sensors)
    explanation=f"Сенсор № {sensors}; Кейс № {object_['caseId']};"
    if(some_text is not None):
        explanation=f"{explanation} {some_text}"
    values.append(explanation)

    data["values"]=values
   
    return(data)

@handler_exception
def filter_for_incident_number_signature(case, artifact , object_ ):
    """
    Фильтр полей JSON документа в поля таблицы incident_number_signature_tables
    """

    snort_sids=dict(columns=("sid","count_alert","id"))
    values=list()
    for item in artifact:
        if (item["dataType"]=="snort_sid"):
            data=item["data"].replace(" ","")
            sids=data.split(",")
            values.extend(sids)         #Боремся с возможными дубликатами номеров сигнатур snort

    snort_sids["values"]=[[x,0] for x in set(values)]
    try:
       snort_sids["values"][0]
    except IndexError as i_err:
        if not i_err.args: 
           i_err.args=('',)
        msg=f"Кейс № {object_['caseId']} - не содержит номеров сигнатур snort"
        i_err.args = (msg,)
        raise(i_err)
    return(snort_sids)

@handler_exception
def filter_for_incident_analyst (case, artifact , object_):
    """
    Фильтр полей JSON документа в поля таблицы incident_analyst_tables
    """
 
    data=dict(columns=("login_name","true_false","count_alert_analyst",
                       "information_analyst","date_time_analyst","id"))
    values=list()
    values.append(get_login(case, artifact , object_)) #извлекаем loggin аналитика
    values.append(get_analysys_decision(case, artifact , object_)) #определяем вердикт
    values.append(0)
    values.append(get_verdict(case, artifact , object_)) # извлекаем текст решения аналитика
    values.append(trim_timestamp(object_["updatedAt"])) # извлекаем время принятия решения
    data["values"]=values
   
    return(data)

def get_rootID(case, artifact , object_):
    rootID=case.get("rootId")
    if rootID is None:
        rootID=object_.get("id")

    return rootID


def get_verdict(case, artifact , object_):
    """
       Функция реализует логику извлечени решения аналитика по событию из исходного Case-документа
    """
    information_analyst=object_["title"]
    if (object_.get("summary") is not None and object_["title"]!=object_["summary"]):
        information_analyst=f"{information_analyst} ( {object_['summary']} )"
    return  information_analyst

def get_login(case, artifact , object_):
    """
       Функция реализует логику поределения login-а аналитика рассматривавшего исходный Case-документ
    """
    loggin=object_["updatedBy"]
    loggin=loggins_accordance.get(loggin,"the_hive1")
    if(isinstance(loggin,dict)):
        loggin=loggin.get("a","the_hive1")
    return(loggin)

def get_analysys_decision(case, artifact , object_):
    """
        Функция реализует логику извлечения решениия аналитика на основе присутствия (отсутствия)
        определенных полей в исходном Case-документе
    """
    if (object_["resolutionStatus"] is None or get_from_obj(case,"details","resolutionStatus") is None):   
        type_attack=object_["customFields"]["class-attack"]["string"]
      #  case_status=attack_type_code.get(type_attack)
       # if (attack_type_code.get(type_attack) is None):
        case_status=resolution_status.get(type_attack,1) # По умолчанию ожидаем только TruePositive и проставляем 1", второе возможное значение FalsePositive
       # else case_status=attack_type_code.get(type_attack)
    else: # Оставляю данную проверку на случай если эти поля будут иметь значения
        case_status=resolution_status.get(object_["resolutionStatus"],4) # 4-соответствует сетевой трафик утерян и косвенно сигнализирует о торм что в данном поле находится какая то лажа
        if(isinstance(case_status,dict)):
            case_status=case_status.get(object_["impactStatus"],4)
    return (case_status)


"""#################################################################################"""
"""    Вспомогательные утилиты   """
"""#################################################################################"""

def date_start_end(data):
    
    for item in data.split("\n"):
        item=item.strip()
        if(item.startswith("Время начала:")):
            date_start=item.replace("Время начала:","")
            date_start=date_start.strip()
        elif(item.startswith("Время окончания:")):
            date_end=item.replace("Время окончания:","").strip()
            date_end=date_end.strip()

    return(date_start, date_end)
    

def trim_timestamp(_timestamp):
    """
    Убираем наносекунды
    """
    if(isinstance(_timestamp,int)):
        _timestamp=str(_timestamp)
    _timestamp=_timestamp[0:10]
    return(_timestamp)

def date_convert_(_timestamp): 
    """
    Конвертируем timestamp в дату
    """
    _timestamp=trim_timestamp(_timestamp)    
    d=datetime.datetime.fromtimestamp(int(_timestamp))

    return(d.strftime('%Y-%m-%d %H:%M:%S'))

def str_to_ip(ip):
    """
    Проверяем корректность ip -адреса
    конвертируем его в число
    """
    try:
        ipaddress.ip_address(ip)
    except ValueError as ip_err:
        if(isinstance(ip,str)):
            ip=ip.split(":")[1]
    ip=int(ipaddress.IPv4Address(ip))   

    return(ip)
  
def get_from_obj(obj, *keys, defaul=None):
    """
    подставляет None если в иерархии объекта
    какой то ключ не обнаружен, нужен для замещения значений 
    при обращении к несуществующему ключу в цепочке вида item1["key1"].item2["key2"]
    """
    try:
        value = obj
        for k in keys:
            value = value.get(k)
        return value
    except AttributeError:
        return (defaul)

"""#################################################################################"""
""" Подгружаемые настройки модуля  """
"""#################################################################################"""

def load_settings_file(dir_path,fname):
    full_path=join(dir_path,fname)
    with open(full_path,'r') as f:
        settings=json.load(f)
    return(settings)

current_dir_path_= dirname(abspath(getsourcefile(lambda:0)))

loggins_accordance=load_settings_file(current_dir_path_,"login_accordance.json")
resolution_status=load_settings_file(current_dir_path_,"resolution_status.json")
attack_type_code=load_settings_file(current_dir_path_,"attack_type_code.json")


str_dst_ip="" #Список ip-адресов назначения (передаю между функциями через глобальную переменную, как по другому ума не приложу)