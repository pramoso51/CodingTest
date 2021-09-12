<?php
header('Content-Type: application/json');
date_default_timezone_set('America/Guatemala');

class EvaluateSchedule
{
    public $_json;
    public $_schedule;
    public $_dateI;
    public $_dateF;
    public $_resultBusy = array();
    public $_available = array();
    public $_result = array();
    public $_initFI;
    public $_initFF;

    function __construct() {
        $this->_initFI = date(date('Y-m-d')." 08:00:00");
        $this->_initFF = date(date('Y-m-d')." 17:00:00");
    }

    function WhileRange()
    {
        $this->_json = '[
            {
                "contact": "Kyle",
                "appointments":
                [
                    {
                        "hour": "09:30"
                    },
                    {
                        "hour": "10:30"
                    }
                ]
            },
            {
                "contact": "Paul",
                "appointments":
                [
                    {
                        "hour": "09:30"
                    },
                    {
                        "hour": "10:30"
                    }
                ]
            },
            {
                "contact": "Alex",
                "appointments":
                [
                    {
                        "hour": "11:30"
                    },
                    {
                        "hour": "12:30"
                    }
                ]
            },
            {
                "contact": "Luis",
                "appointments":
                [
                    {
                        "hour": "11:30"
                    },
                    {
                        "hour": "12:30"
                    }
                ]
            },
            {
                "contact": "Jairo",
                "appointments":
                [
                    {
                        "hour": "13:30"
                    },
                    {
                        "hour": "14:30"
                    }
                ]
            },
            {
                "contact": "Sonia",
                "appointments":
                [
                    {
                        "hour": "13:30"
                    },
                    {
                        "hour": "14:00"
                    }
                ]
            }
            ]';

        $this->_schedule   = json_decode($this->_json);
        $this->_dateI      = $this->_initFI;
        $this->_dateF      = $this->_initFF;
        
        while ($this->_dateI <= $this->_dateF)
        {
            ###########################
            # Calculate a next date
            ###########################
            $_dateN = new DateTime($this->_dateI);
            $_dateN->add(new DateInterval('PT30M'));
            $_dateN = $_dateN->format('Y-m-d H:i:s');

            ###########################
            # Review Date vrs DateN
            ###########################
            $this->CheckCalendar($this->_dateI, $_dateN);
            $this->_dateI = $_dateN;
        }
    
        ####################################
        ## Salida General del Proceso
        ####################################
        $this->EvaluaFree();
        print_r($this->_result);
    }

    function CheckCalendar($_dateI, $_dateN)
    {
        ####################################
        ## Checa Calendario Ocupado
        ####################################

        $_schedule = $this->_schedule;
        $this->_resultBusy[$_dateI] = null;
        $_resultBusyContact = array();

        foreach($_schedule as $meeting)
        {
            $_contact = $meeting->contact;

            foreach($meeting->appointments as $appointment)
            {
                $_dateC = date(date('Y-m-d')." ".$appointment->hour.":00");
                $_baseDate  = strtotime($_dateC);
                $_beginDate = strtotime($_dateI);
                $_endDate   = strtotime($_dateN);

                if($_baseDate >= $_beginDate && $_baseDate <= $_endDate) 
                {
                    array_push($_resultBusyContact, $_contact);
                }
            }
        }

        $this->_resultBusy[$_dateI] = $_resultBusyContact;
    }

    function EvaluaFree()
    {
        ####################################
        ## Checa Disponibilidades
        ####################################

        $_schedule         = $this->_schedule;
        $this->_dateI      = $this->_initFI;
        $this->_dateF      = $this->_initFF;
        
        while ($this->_dateI <= $this->_dateF)
        {
            $_disponibles = array();
            $this->_available[$_dateI] = null;

            ###########################
            # Calculate a next date
            ###########################
            $_dateN = new DateTime($this->_dateI);
            $_dateN->add(new DateInterval('PT30M'));
            $_dateN = $_dateN->format('Y-m-d H:i:s');
            $_cantContacts = count($_schedule);
            $_c = $_cantContacts;
    
            $_disponibles = array();
            foreach($_schedule as $meeting)
            {
                $_contact = $meeting->contact;
                $_aplica = 1;
                foreach($this->_resultBusy[$this->_dateI] as $_contactsBusy)
                {
                    if ($_contact == $_contactsBusy)
                    {
                        $_c--;
                        $_aplica = 0;
                    }
                }

                if ($_aplica == 1)
                {
                    array_push($_disponibles, $_contact);
                }
            }

            if ($_cantContacts >= 3)
            {
                
                $this->_result[] = array("date"=>$this->_dateI, "cant_disponibles"=>$_c, "contactos_disponibles"=>$_disponibles);
            }

            $this->_dateI = $_dateN;
        }
    }
}

#############################################
## Main
#############################################
$_EvaluateSchedule = new EvaluateSchedule();
$_EvaluateSchedule->WhileRange();


?>