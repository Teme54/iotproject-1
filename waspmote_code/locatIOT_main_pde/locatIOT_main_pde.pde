/*

LocatIoT Waspmote koodi


-GPS alustus
-2G alustus
-MQTT paketin generointi

*/

#include "WaspGPRS_SIM928A.h"

#include "locat_functions.h"

#define VIBES 5 //how many inertial interrupts must occur before movement is detected
#define ACC_SENSITIVITY 200 //intertial interrupt sensitivity
#define TRACKING_LENGTH 2	//how many times device will do tracking, before going back to stationary, IF there was no vibration during the time

unsigned int wake_wait = 5000;

unsigned long intWait = 0;

uint8_t trackingCounter = 0;

unsigned long previousMillis = 0; //ACCEL 
int intCount = 0;  //ACCEL 

char apn[] = "";
char login[] = "";
char password[] = "";

char client_id[20] = "LocatIoT-waspmote";  //MQTT client ID

char topic[10] = "testi"; //MQTT topic
char msg[50] = "0.000000-0.000000"; //MQTT viesti

uint8_t mqtt_packet[100], packet_len=0;

//double test_lat = 65.006405, test_lon = 24.002345;

uint8_t sendData = 0, trackingMode = 0;
int8_t answer = 0;


void setup()
{
  
    USB.ON();
    USB.println(F("USB port started..."));
    GPRS_SIM928A.set_APN(apn, login, password);   
}


void accel(){
  
  	// 1. Starts accelerometer
  	ACC.ON();
  
 	if(trackingMode == 0)
	{
		USB.println(F("Stationary mode"));
	}
	else
	{

		USB.println(F("Tracking mode"));
		USB.println(trackingCounter,DEC);
	}
 
 	if( intFlag & ACC_INT ) //if woken up by accelerometer
	{
	    // clear interruption flag
	    intFlag &= ~(ACC_INT);
	      
	    viive();

	}
	else 
	{
		sendData = 1; //send data even if there was no vibration after waking up from stationary mode sleep
		ACC.setIWU(ACC_SENSITIVITY); 
		if(trackingMode) trackingCounter++;
		if(trackingCounter > TRACKING_LENGTH) {trackingMode = 0; trackingCounter = 0;}
		
	}
}
void viive(){

    previousMillis = millis();
    while((millis() - previousMillis) < wake_wait){
    //USB.println(intCount);
      	rupt();
      	if(intCount > VIBES) break;
        
    }
    
    if(intCount <= VIBES){ //not enough vibration
		sendData = 0;
		intCount = 0;
           
    }
    else if(intCount > VIBES) {
    
    	sendData = 1; 
	    if(trackingMode == 0)
	    {
	    	trackingMode = 1; trackingCounter = 0;
	    }
    
    	USB.println(F("Enough vibration detected. Tracking mode is enabled"));
    	intCount = 0;
    
    }
}
void rupt(){

   //in this function check if ACC_INT flag is set
	if( intFlag & ACC_INT )
	{
	    // clear interruption flag
	    intFlag &= ~(ACC_INT);
	   	intCount++; 
	    USB.println(F("++ ACC interrupt detected ++"));
	    //USB.println(intCount);

	} 

	ACC.ON();
	ACC.setIWU(ACC_SENSITIVITY); //set inertial wakeup interrupt on again
}

void loop()
{

  accel(); //start ACC interrupt and start polling it

 if(sendData) //if interrupt polling function has decided that we should send data
  {

    // 2. activates the GPRS_SIM928A module:

  	USB.print(F("Battery Level: "));
    USB.print(PWR.getBatteryLevel(),DEC);

    //usb serial to RF UART
    Utils.setMuxSocket1();
    
    beginSerial(9600,1);

    answer = GPRS_SIM928A.ON();
        //answer = 5;
    if ((answer == 1) || (answer == -3))
    {

    locateGPS(); //get GPS location and save location message to msg
    parse_mqtt(topic, msg, strlen(topic), strlen(msg)); //parse the message to MQTT protocol format
  	ConnectAndSendData();

  	}

	  	 else //if module ready
	  { 
	    USB.println("GPS fail");
	  }


  
   }
  
  else //sendData
  {
  
    USB.println(F("There was no vibration, don't send anything"));
    
  }
			if(trackingMode == 0)
			{
				USB.println(F("Going to stationary mode sleep for 15 sec"));
				ACC.ON();
    		ACC.setIWU(ACC_SENSITIVITY);
				PWR.deepSleep("00:00:30:00", RTC_OFFSET, RTC_ALM1_MODE1, ALL_OFF);
			}
			else
			{
				USB.println(F("Going to tracking mode sleep for 5 sec"));
				PWR.deepSleep("00:00:01:00", RTC_OFFSET, RTC_ALM1_MODE1, ALL_OFF);
				
			}
			
}

