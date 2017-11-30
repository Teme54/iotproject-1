/*

LocatIoT Waspmote koodi


-GPS alustus
-2G alustus
-MQTT paketin generointi

*/

#include "WaspGPRS_SIM928A.h"
boolean poistu = true;
unsigned int kierto = 5000;

 unsigned long intWait = 0;
 
 unsigned int interval = 3000;
 unsigned long previousMillis = 0; //ACCEL 
volatile int intCount = 0;  //ACCEL 

char apn[] = "apn.moimobile.fi";
char login[] = "";
char password[] = "";

char client_id[20] = "LocatIoT-waspmote";  //MQTT client ID

char topic[10] = "testi"; //MQTT topic
char msg[50] = "0.000000-0.000000"; //MQTT viesti

uint8_t packet_len=0;

uint8_t mqtt_packet[100];

int8_t answer;

uint32_t current_lat=0, current_lon=0;

//double test_lat = 65.006405, test_lon = 24.002345;

uint8_t sendData = 1, sleeping = 0;


//function for generating the MQTT TCP packet

void parse_mqtt(char *topic, char *msg, uint8_t topic_lt, uint8_t msg_lt) 
{

    //USB.println(topic);USB.println(msg);
    
    uint8_t clientid_lt = sizeof(client_id)-1;
    
    uint8_t connect_command[] = {0x10, 0x00, 0x00, 0x06, 0x4d, 0x51, 0x49, 0x73, 0x64, 0x70, 0x03, 0x02, 0x00, 0x3c, 0x00, 0x00};

    connect_command[1] = clientid_lt + 14; connect_command[15] = clientid_lt;
   
    uint8_t packet_ptr = 0; //this is a pointer to the end of the packet string
    
    memmove(mqtt_packet,connect_command,sizeof(connect_command)); //move connect command header to the beginning of mqtt_packet
    packet_ptr += sizeof(connect_command); //increment packet pointer by the added data length so we know where to write to the packet next
        
    memmove(mqtt_packet+packet_ptr, client_id, sizeof(client_id) ); //add client id next
    packet_ptr += clientid_lt;
    
    uint8_t w_buf[5] = {0x30, 0x00, 0x00, topic_lt}; //initialise a temporary work buffer. Put in publish command 0x30, length of publish message, topic length MSB and LSB
    
    w_buf[1] = topic_lt+msg_lt+2; //replace 0 with length of publish message

    memmove(mqtt_packet+packet_ptr, w_buf, 4);
    packet_ptr +=4;
    
    memmove(mqtt_packet+packet_ptr, topic, topic_lt);
    packet_ptr +=topic_lt;
    
    memmove(mqtt_packet+packet_ptr, msg, msg_lt);
    packet_ptr +=msg_lt;
    
    packet_len = packet_ptr;
    
    for(uint8_t p=0;p<packet_len;p++)
    {
    
    USB.print(mqtt_packet[p],HEX);
    USB.print(" ");
    
    if(p>0 && (p%15 == 0))USB.print("\n");
    
    }

  
}

void setup()
{
  
     // setup for Serial port over USB:
    USB.ON();
    USB.println(F("USB port started..."));

    // 1. sets operator parameters
    GPRS_SIM928A.set_APN(apn, login, password);

 
    
}

void locateGPS()
{
  
    int8_t GPS_status = GPRS_SIM928A.GPS_ON();
    if (GPS_status == 1)
    { 
        USB.println(F("GPS started. Waiting for GPS acquisition for 200 seconds"));
        
    }
    else
    {
        USB.println(F("GPS NOT started"));   
    }

  if ((GPS_status == 1) && (GPRS_SIM928A.waitForGPSSignal(200) == 1))
    {
        // 5. reads GPS data
        int8_t answer = GPRS_SIM928A.getGPSData(1);

        if (answer == 1)
        {
            // 6. Shows all GPS data collected          
            USB.print(F("Latitude (in degrees): "));
            USB.print(GPRS_SIM928A.latitude);
            USB.print(F("\t\tLongitude (in degrees): "));
            USB.println(GPRS_SIM928A.longitude);
                        
            USB.print(F("\t\tSatellites in use: "));
            USB.println(GPRS_SIM928A.sats_in_use, DEC);

      current_lat = (long int)(GPRS_SIM928A.latitude*100000); //take the coordinate values and convert to integers(save decimals by multiplying)
      current_lon = (long int)(GPRS_SIM928A.longitude*100000); //65.543222 -> 6554322
  
      uint32_t whole_buf = 0; uint32_t tenth_buf=0;

      char lat_str[10], lon_str[10];
      char tenth_str[10];

      whole_buf = (uint32_t)GPRS_SIM928A.latitude;  
      tenth_buf = current_lat-((whole_buf-1)*100000);

      sprintf(lat_str,"%ld.",whole_buf);

      sprintf(tenth_str,"%ld",tenth_buf);

      memmove(lat_str+3,tenth_str+1,strlen(tenth_str));

      USB.println("************");
      USB.println(lat_str);

      whole_buf = (uint32_t)GPRS_SIM928A.longitude;
      tenth_buf = current_lon-((whole_buf-1)*100000);

      sprintf(lon_str,"%ld.",whole_buf);

      sprintf(tenth_str,"%ld",tenth_buf);

      memmove(lon_str+3,tenth_str+1,strlen(tenth_str));

      strcat(lat_str,"-");
      strcat(lat_str,lon_str);
      memmove(msg,lat_str,strlen(lat_str));

      USB.println("Location message ready:");

      USB.println(msg);

        sendData = 1;
      
        }    
    }
    else
    {
        USB.println(F("GPS not started"));
  sendData = 0;  
              
    }
                        
}
void accel(){
  
  // 1. Starts accelerometer
 
  ACC.ON();
  
 //ACC.setMode(ACC_LOW_POWER_1); //

  // 2. Enable interruption: Inertial Wake Up

  ACC.setIWU(450); 
   USB.println(intCount);
  viive();
  // 3. Set low-power consumption state

   
  
}
void viive(){

   
    
    previousMillis = millis();
    while((millis() - previousMillis) < kierto){
    USB.println(intCount);
      rupt();
      if(intCount > 10) break;
        
    }
    if(intCount <= 10){
      sendData = 0;
      intCount = 0;
    }
    else if(intCount > 10) {
    sendData = 1;
     USB.println(F("paivaa"));
    intCount = 0;
    }
}
void rupt(){

   // Interruption event happened
  // 4. Disable interruption: Inertial Wake Up
  //    This is done to avoid new interruptions
    // 5. Check the interruption source
    
   // ACC.unsetIWU();
        if( intFlag & ACC_INT )
  {
    // clear interruption flag
    intFlag &= ~(ACC_INT);
   intCount = intCount + 1; 
    USB.println(F("++ ACC interrupt detected ++"));
    USB.println(intCount);
  //( 6. Clear interruption pin   

  // This function is used to make sure the interruption pin is cleared
  // if a non-captured interruption has been produced
 
} 
ACC.ON();
ACC.setIWU(450);
}
void loop()
{
  accel();

  // 1. Starts accelerometer
 if(sendData)
  {
  
    // 2. activates the GPRS_SIM928A module:

  USB.print(F("Battery Level: "));
    USB.print(PWR.getBatteryLevel(),DEC);

    //usb serial to RF UART
    Utils.setMuxSocket1();
    
    beginSerial(9600,1);

  answer = GPRS_SIM928A.ON();

    if ((answer == 1) || (answer == -3))
    {
        USB.println(F("GPRS_SIM928A module ready..."));

    locateGPS(); //get GPS location and save location message to msg
    parse_mqtt(topic, msg, strlen(topic), strlen(msg)); //parse the message to MQTT protocol format


  

    // 3. sets pin code:
    USB.println(F("Setting PIN code..."));
    if (GPRS_SIM928A.setPIN("0000") == 1) 
    {
        USB.println(F("PIN code accepted"));
    }
    else   {
        USB.println(F("PIN code incorrect"));
    }

    // 4. waits for connection to the network:
    answer = GPRS_SIM928A.check(180);    
    if (answer == 1)
    {
        USB.println(F("GPRS_SIM928A module connected to the network..."));

        // 5. configures IP connection
        USB.print(F("Setting connection..."));
        answer = GPRS_SIM928A.configureGPRS_TCP_UDP(SINGLE_CONNECTION, NON_TRANSPARENT);
        if (answer == 1)
        {
      USB.println(F("Done"));

      // if configuration is success shows the IP address
      USB.print(F("Configuration success. IP address: ")); 
      //USB.println(GPRS_SIM928A.IP_dir);
      USB.print(F("Opening TCP socket..."));  

      // 6. create a TCP socket to server 
      
      answer = GPRS_SIM928A.createSocket(TCP_CLIENT, "139.59.155.145", "1883");
      if (answer == 1)
      {
          USB.println(F("Connected"));


          //************************************************
          //             Send the MQTT packet
          //************************************************

          USB.print(F("Sending MQTT packet"));
          // 7. sending 'test_string'
          if (GPRS_SIM928A.sendData(mqtt_packet, packet_len) == 1) 
          {
              USB.println(F("Done"));
          }
          else{
              USB.println(F("Fail"));
          }

          USB.print(F("Closing TCP socket..."));  
          // 9. closes socket
          if (GPRS_SIM928A.closeSocket() == 1) 
          {
              USB.println(F("Done"));
          }
          else   {
              USB.println(F("Fail"));
          }
      }

      else if (answer == -2)
      {
          USB.print(F("Connection failed. Error code: "));
          USB.println(answer, DEC);
          USB.print(F("CME error code: "));
          USB.println(GPRS_SIM928A.CME_CMS_code, DEC);
      }
      else {
          USB.print(F("Connection failed. Error code: "));
          USB.println(answer, DEC);
      }  
         
        }
        else if (answer < -14)
        {
      USB.print(F("Configuration failed. Error code: "));
      USB.println(answer, DEC);
      USB.print(F("CME error code: "));
      USB.println(GPRS_SIM928A.CME_CMS_code, DEC);
        }

        else {
      USB.print(F("Configuration failed. Error code: "));
      USB.println(answer, DEC);
        }

    }
    else{
        USB.println(F("GPRS_SIM928A module cannot connect to the network"));     
    }
      
  }

  else
  { 
    USB.println("GPS fail");
  }

  
  //if module ready

    // Power down GPRS module completely. There is also sleep mode but this consumes way more current than should (50-60mA)
  // maybe on tracking mode could use sleep mode, because then it can get GPS location faster(hot start)
    GPRS_SIM928A.OFF(); 

    USB.println(F("Sleeping..."));
    sleeping = 1;

  }
    // 11. sleeps one hour
    //PWR.deepSleep("00:01:00:00", RTC_OFFSET, RTC_ALM1_MODE1, ALL_OFF);
      if(intCount == 0){
        ACC.ON();
        ACC.setIWU(450);
          USB.println(F("Waspmote goes into sleep mode until the Accelerometer causes an interrupt"));
          PWR.sleep(ALL_OFF);  
      }
}

