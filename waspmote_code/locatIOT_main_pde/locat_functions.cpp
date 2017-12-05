#include "locat_functions.h"

#include <stdint.h>

#include "Waspmote.h"
#include "WaspGPRS_Pro_core.h"
#include "WaspGPRS_SIM928A.h"

uint32_t current_lat=0, current_lon=0;
extern int8_t answer;

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
        answer = GPRS_SIM928A.getGPSData(1);

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

void ConnectAndSendData(){


    USB.println(F("GPRS_SIM928A module ready..."));

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

                        //send MQTT packet
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

                    else if (answer == -2) //createSocket return value
                    {
                        USB.print(F("Connection failed. Error code: "));
                        USB.println(answer, DEC);
                        USB.print(F("CME error code: "));
                        USB.println(GPRS_SIM928A.CME_CMS_code, DEC);
                    }
                    else { //createSocket return
                        USB.print(F("Connection failed. Error code: "));
                        USB.println(answer, DEC);
                    }  
               
              }
              else if (answer < -14) //configure GPRS TCP UDP connection return
              {
                        USB.print(F("Configuration failed. Error code: "));
                        USB.println(answer, DEC);
                        USB.print(F("CME error code: "));
                        USB.println(GPRS_SIM928A.CME_CMS_code, DEC);
              }

              else 
              { //configure GPRS TCP UDP return
                        USB.print(F("Configuration failed. Error code: "));
                        USB.println(answer, DEC);
              }

        }
    
    else
    { //GPRS connection check return
        USB.println(F("GPRS_SIM928A module cannot connect to the network"));     
    }
    
    // Power down GPRS module completely. There is also sleep mode but this consumes way more current than should (50-60mA)
// maybe on tracking mode could use sleep mode, because then it can get GPS location faster(hot start)
    if(trackingMode)
    {
    GPRS_SIM928A.setMode(GPRS_PRO_MIN);
    }
    else
    {
    GPRS_SIM928A.OFF(); 
    }
    
    
    USB.println(F("Sleeping..."));
                  
    }

 