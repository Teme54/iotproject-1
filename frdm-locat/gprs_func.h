#ifndef GPRS_FUNC_H_

#define GPRS_FUNC_H_



void GPRS_setPin(const char *pin);

void GPRS_checkNetwork();
void GPRS_checkGPRSAttachment();
void GPRS_configureIP();
void GPRS_createTCPSocket();

void GPRS_sendData();

#endif