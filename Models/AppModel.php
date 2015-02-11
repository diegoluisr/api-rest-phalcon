<?php
namespace Api\Models;

use Phalcon\Mvc\Model,
    Phalcon\Annotations\Adapter\Memory,
    Phalcon\Mvc\Model\Validator\Uniqueness,
    Phalcon\Mvc\Model\Validator\Email as EmailValidator,
    Phalcon\Mvc\Model\Validator\StringLength as StringLengthValidator,
    Phalcon\Mvc\Model\Validator\PresenceOf as PresenceOfValidator,
    Phalcon\Mvc\Model\Validator\Regex as RegexValidator;

class AppModel extends Model {

    public function validation() {
        $clasname = get_class($this);
        $reader = new Memory();
        $reflector = $reader->get($clasname);

        $fileds = $reflector->getPropertiesAnnotations();

        foreach ($fileds as $field => $collection) {
            foreach ($collection as $annotation) {
                switch($annotation->getName()){
                    case 'Primary':

                        break;
                    case 'Unique':
                        $this->validate(new Uniqueness(
                            array(
                                'field' => $field,
                                'message' => 'The field %field% must be unique'
                            )
                        ));
                        break;
                    case 'Column':
                        $arguments = $annotation->getArguments();
                        $this->validateColumn($field, $arguments);
                        break;
                }
            }
        }

        if ($this->validationHasFailed() == true) {
            return false;
        }
        return true;
    }

    protected function validateColumn($field, $arguments) {
        if(isset($arguments['type'])){
            switch($arguments['type']) {
                case 'integer':
                     $this->validate(new RegexValidator(
                        array(
                            'field' => $field,
                            'pattern' => '/^[a-zA-Z0-9]*$/',
                            'message' => 'The field %field% must be a integer'
                        )
                    ));
                break;
                case 'email':
                    $this->validate(new EmailValidator(
                        array(
                            'field' => $field,
                            'message' => 'The field %field% must be a valid email'
                        )
                    ));
                break;
                case 'string':
                    $min = 1;
                    $max = 45;
                    
                    if(isset($arguments['min'])){
                        $min = intval($arguments['min']);
                    }
                    if(isset($arguments['max'])){
                        $max = intval($arguments['max']);
                    }
                    if($min > $max) {
                        $min = $max;
                    }
                    $this->validate(new StringLengthValidator(
                        array(
                            'field' => $field,
                            'max' => $max,
                            'min' => $min,
                            'messageMaximum' => 'The field %field% is too large',
                            'messageMinimum' => 'The field %field% is too short'
                        )
                    ));
                break;
                case 'datetime':
                    $this->validate(new RegexValidator(
                        array(
                            'field' => $field,
                            'pattern' => '/^[0-9]{4}-(0[0-9]|1[0-2])-(0[0-9]|[1-2][0-9]|3[0-1]) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/',
                            'message' => 'The field %field% must be Y-m-d H:i:s format'
                        )
                    ));
                break;
            }
        }
        if(isset($arguments['nullable']) && $arguments['nullable'] == false) {
            $this->validate(new PresenceOfValidator(
                array(
                    'field' => $field,
                    'message' => 'The field %field% is required'
                )
            ));
        }
    }
}