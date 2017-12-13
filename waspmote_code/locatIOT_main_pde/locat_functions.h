
#ifndef LOCAT_FUNCTIONS_H_
#define LOCAT_FUNCTIONS_H_

#endif

#include <stdint.h>
#include <string.h>

//#include "WaspGPRS_Pro_core.h"


extern char client_id[20];
extern char topic[10];
extern char msg[50];
extern uint8_t packet_len, trackingMode, sendData;



extern uint8_t mqtt_packet[100]; 



//extern class GPRS_SIM928A;

void parse_mqtt(char *topic, char *msg, uint8_t topic_lt, uint8_t msg_lt);
void locateGPS();

void ConnectAndSendData();