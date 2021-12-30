import unittest
from _case_json_parser import json_fields_filter
from _case_json_parser import filter_for_incident_chief
from _case_json_parser import filter_for_incident_additional
from _case_json_parser import filter_for_incident_number_signature
from _case_json_parser import filter_for_incident_analyst

import json
import datetime

test_json_set_path="json_data/1617691152_2021_04_06_09_39_12_~690053144.case.txt"


class Test_case_json_parser (unittest.TestCase):
  def setUp(self):
       with open(test_json_set_path,"r") as test_f:
           data=json.load(test_f)

       case=data["Case"]
       artifact=data["Artifact"]
       object_=case["object"]
       (ic,some_text)=filter_for_incident_chief(case, artifact , object_ )
       ins=filter_for_incident_number_signature(case, artifact , object_ )
       iadd=filter_for_incident_additional(case, artifact , object_, some_text)
       ia=filter_for_incident_analyst(case, artifact , object_ )
       process_d= datetime.datetime.now()

       filtered_fields=dict(incident_chief=ic,
                         incident_additional=iadd,
                         incident_numbers_signature=ins,
                         incident_analyst=ia,
                         root_id=case["rootId"],
                         processing_data=process_d.strftime('%Y-%m-%d %H:%M:%S'))          
          
        
  def test_filds_for_filter_for_incident_chief(self):
    self.assertIsNone(ic["values"], "Не должно быть nil")
  #def test_subtract(self):
  #  self.assertEqual(self.calculator.subtract(10,5), 5)
  #def test_multiply(self):
  #  self.assertEqual(self.calculator.multiply(3,7), 21)
  #def test_divide(self):
  #  self.assertEqual(self.calculator.divide(10,2), 5)
# Executing the tests in the above test case class
if __name__ == "__main__":
  unittest.main()
