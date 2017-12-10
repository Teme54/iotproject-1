
#ifndef LOCAT_FUNCTIONS_H_
#define LOCAT_FUNCTIONS_H_

#endif

extern char client_id[20];
extern char topic[10];
extern char msg[50];
extern uint8_t packet_len, trackingMode, sendData;


extern char mqtt_packet[100]; 


void parse_mqtt(char *topic, char *msg, uint8_t topic_lt, uint8_t msg_lt);
