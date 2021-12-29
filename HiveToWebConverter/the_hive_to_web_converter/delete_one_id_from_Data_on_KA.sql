set @id_= 28854;
DELETE from incident_additional_tables where id=@id_;
DELETE from incident_chief_tables where id=@id_;
DELETE from incident_number_signature_tables where id=@id_;
DELETE from incident_analyst_tables where id=@id_;
DELETE from processed_cases_table where id=@id_
COMMIT;
