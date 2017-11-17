/*

LocatIoT Waspmote koodi


-GPS alustus
-2G alustus
-MQTT paketin generointi









*/

#include "WaspGPRS_SIM928A.h"
#include <WaspFrame.h>

char apn[] = "data.moimobile.fi";
char login[] = "login";
char password[] = "password";


char client_id[] = "LocatIoT-waspmote";  //MQTT client ID

char topic[] = "testi"; //MQTT topic
char msg[] = "terveseppo2"; //MQTT viesti

uint8_t packet_len=0;

uint8_t mqtt_packet[100];

int8_t answer;

int current_lat=6524, current_lon=2522;



//function for generating the MQTT TCP packet

void parse_mqtt(char *topic, char *msg, uint8_t topic_lt, uint8_t msg_lt) 
{
  
	  //zero out the packet
	  memset(mqtt_packet,0,100);
	  
	  uint8_t connect_command[] = {0x10, 0x00, 0x00, 0x06, 0x4d, 0x51, 0x49, 0x73, 0x64, 0x70, 0x03, 0x02, 0x00, 0x3c};
	  
	  connect_command[1] = sizeof(client_id)-1+14; //put size of payload(client id) and +14 bytes to the header
	 
	  uint16_t packet_ptr = 0;
	  
	  memmove(mqtt_packet,connect_command,sizeof(connect_command)); //move connect command header to the beginning of mqtt_packet
	  packet_ptr += sizeof(connect_command);
	  
	  mqtt_packet[packet_ptr] = 0x00; //put client id length MSB
	  mqtt_packet[packet_ptr+1] = sizeof(client_id)-1; //put client id length LSB
	  packet_ptr +=2;
	  
	  memmove(mqtt_packet+packet_ptr, client_id, sizeof(client_id) ); //add client id next
	  packet_ptr += sizeof(client_id)-1;
	  
	  uint8_t w_buf[10] = {0x30, 0x00}; //initialise a temporary work buffer. Put in publish command 0x30 and 0
	  
	  w_buf[1] = topic_lt-1+msg_lt-1+2; //replace 0 with length of publish message
	  
	  memmove(mqtt_packet+packet_ptr, w_buf, 2);
	  packet_ptr +=2;
	
		w_buf[0] = 0x00; //topic length MSB
		w_buf[1] = topic_lt-1;

		memmove(mqtt_packet+packet_ptr, w_buf, 2);
	  packet_ptr +=2;
	  
	  memmove(mqtt_packet+packet_ptr, topic, topic_lt);
	  packet_ptr +=topic_lt-1;
	  
	  memmove(mqtt_packet+packet_ptr, msg, msg_lt);
	  packet_ptr +=msg_lt-1;
	  
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
  
     
    USB.println(F("**************************"));
    // 1. sets operator parameters
    GPRS_SIM928A.set_APN(apn, login, password);
    // And shows them
    GPRS_SIM928A.show_APN();
    USB.println(F("**************************"));
     
    
}

void locateGPS()
{
  
  	int8_t GPS_status = GPRS_SIM928A.GPS_ON();
    if (GPS_status == 1)
    { 
        USB.println(F("GPS started"));
        
    }
    else
    {
        USB.println(F("GPS NOT started"));   
    }

	if ((GPS_status == 1) && (GPRS_SIM928A.waitForGPSSignal(30) == 1))
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
            USB.print(F("Date: "));
            USB.print(GPRS_SIM928A.date);
            USB.print(F("\t\tUTC_time: "));
            USB.println(GPRS_SIM928A.UTC_time);
            
            
            USB.print(F("\t\tSatellites in use: "));
            USB.println(GPRS_SIM928A.sats_in_use, DEC);
            USB.print(F("\t\tSatellites in view: "));
            USB.println(GPRS_SIM928A.sats_in_use, DEC); 

			current_lat = (int)(GPRS_SIM928A.latitude*100);
			current_lon = (int)(GPRS_SIM928A.longitude*100);
	
			int whole_buf = 0; int tenth_buf=0;

			char lat_str[10], lon_str[10];

			whole_buf = current_lat/100;  
			tenth_buf = current_lat-(whole_buf*100);

			itoa(whole_buf,lat_str,10); //put whole of latitude to lat_str
			lat_str[2] = '.'; //dot

			itoa(tenth_buf,lat_str+3,10); //put tenths of latitude to lat_str

			msg[5] = '-';

			whole_buf = current_lon/100;
			tenth_buf = current_lon-(whole_buf*100);

			itoa(whole_buf,lon_str,10);
			lon_str[2] = '.';

			itoa(tenth_buf,lon_str+3,10);

			strcpy(msg,"locationdata-lat-");
			strcat(msg,lat_str);
			strcat(msg,"-lon-");
			strcat(msg,lon_str);

			USB.println("*****");

		    USB.println(msg);
			
        }    
    }
    else
    {
        USB.println(F("GPS not started"));  
              
    }
                
        
}

void loop()
{

    // setup for Serial port over USB:
    USB.ON();
    USB.println(F("USB port started..."));
    USB.println(F("**************************"));

    // 2. activates the GPRS_SIM928A module:
    answer = GPRS_SIM928A.ON(); 
    if ((answer == 1) || (answer == -3))
    {
        USB.println(F("GPRS_SIM928A module ready..."));

		locateGPS();

		int8_t u = strlen(msg);
		USB.println("strlen");
		USB.println(u);

		parse_mqtt(topic, msg, sizeof(topic), strlen(msg));

        // 3. sets pin code:
        USB.println(F("Setting PIN code..."));
        // **** must be substituted by the SIM code
        if (GPRS_SIM928A.setPIN("1234") == 1) 
        {
            USB.println(F("PIN code accepted"));
        }
        else
        {
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
                USB.println(GPRS_SIM928A.IP_dir);
                USB.print(F("Opening TCP socket..."));  

                // 6. create a TCP socket
                // “IP” and “port” must be substituted by the IP address and the port
                answer = GPRS_SIM928A.createSocket(TCP_CLIENT, "139.59.155.145", "1883");
                if (answer == 1)
                {
                    USB.println(F("Conected"));


                    //************************************************
                    //             Send a string of text
                    //************************************************

                    USB.print(F("Sending test string..."));
                    // 7. sending 'test_string'
                    if (GPRS_SIM928A.sendData(mqtt_packet, packet_len) == 1) 
                    {
                        USB.println(F("Done"));
                    }
                    else
                    {
                        USB.println(F("Fail"));
                    }

                    

                    USB.print(F("Closing TCP socket..."));  
                    // 9. closes socket
                    if (GPRS_SIM928A.closeSocket() == 1) 
                    {
                        USB.println(F("Done"));
                    }
                    else
                    {
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
                else 
                {
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
            else 
            {
                USB.print(F("Configuration failed. Error code: "));
                USB.println(answer, DEC);
            }
        }
        else
        {
            USB.println(F("GPRS_SIM928A module cannot connect to the network"));     
        }
    }
    else
    {
        USB.println(F("GPRS_SIM928A module not ready"));    
    }

    // 10. powers off the GPRS_SIM928A module
    GPRS_SIM928A.OFF(); 

    USB.println(F("Sleeping..."));

    // 11. sleeps one hour
    PWR.deepSleep("00:00:00:20", RTC_OFFSET, RTC_ALM1_MODE1, ALL_OFF);

}




