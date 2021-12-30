FROM python:3.6
LABEL mainteiner="ivi"

FROM python:3
LABEL mainteiner="ivi"
WORKDIR /usr/src/the_hive_to_web_converter
COPY requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt
COPY ./the_hive_to_web_converter/. .
VOLUME [ "/data/the_hive_to_web_converter_logs" ]
RUN sudo cronyab -e 

FROM mariadb:latest
