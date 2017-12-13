
#include "mbed.h"
#include "gprs_func.h"
#include "at_commands.h"



extern char SimcomRecBuf[];
extern void printSimcomBuffer(char ok);
extern uint8_t sendATCommand(char *command, char *answer, const char *expectedAnswer);
extern void printSimcomBuffer(char ok);
extern void sendSerialData(const char *daatta, uint8_t len);

extern Serial pc;

void GPRS_setPin(const char *pin)
{

	char response=0;

	char command[20];
	memset(command,0x00,sizeof(command));

	strcat(command,AT_CPIN);
	strcat(command,pin);

	//pc.puts(command);

	response = sendATCommand(&command[0],&SimcomRecBuf[0], "OK");

	if(response == 2)
	{
		pc.printf("PIN code correct\r\n");
	}
	else
	{
		pc.printf("Fail setting PIN code\r\n");
	}

	printSimcomBuffer(response);

}

void GPRS_checkNetwork()

{

	char response=0;

	while(response != 2)
	{
	response = sendATCommand(&AT_CREG[0],&SimcomRecBuf[0], "REG: 0,1");
	printSimcomBuffer(response);
	
	}

	
}

void GPRS_checkGPRSAttachment()
{
	char response=0;

	while(response != 2)
	{
	response = sendATCommand(&AT_CGATT[0],&SimcomRecBuf[0], "ATT: 1");
	printSimcomBuffer(response);
	
	}

}

void GPRS_configureIP()
{

	/*

	send CSTT
	send CIICR

	*/
	char response=0;

	while(response != 2)
	{
	response = sendATCommand(&AT_CSTT[0],&SimcomRecBuf[0], "OK");
	printSimcomBuffer(response);
	
	}

	response = 0;
	
	while(response != 2)
	{
	response = sendATCommand(&AT_CIPSTATUS[0],&SimcomRecBuf[0], "START");
	printSimcomBuffer(response);
	}

	response = 0;
	
	response = sendATCommand(&AT_CIICR[0],&SimcomRecBuf[0], "OK");
	printSimcomBuffer(response);


	response = 0;

	while(response != 2)
	{
	response = sendATCommand(&AT_CIPSTATUS[0],&SimcomRecBuf[0], "GPRSACT");
	printSimcomBuffer(response);
	}

	response = 0;

	while(response == 0)
	{
	response = sendATCommand(&AT_CIFSR[0],&SimcomRecBuf[0], "FF");
	printSimcomBuffer(response);
	
	}

	response = 0;

	while(response != 2)
	{
	response = sendATCommand(&AT_CIPSTATUS[0],&SimcomRecBuf[0], "IP STATUS");
	printSimcomBuffer(response);
	}
}


void GPRS_createTCPSocket()
{

	char response=0;
	wait(0.5);

	//while(response ==0)
	//{
	response = sendATCommand(&AT_CIPSTART[0],&SimcomRecBuf[0], "CONNECT OK");
	printSimcomBuffer(response);
	
	//}

}

void GPRS_sendData(const char *daatta, uint8_t len)

{
	char response = 0;
		
	char buffer[3];

	sprintf(buffer,"%d",len);

	char tmpbuf[20];
	memset(tmpbuf,0x00,sizeof(tmpbuf));

	strcat(tmpbuf,AT_CIPSEND);
	memmove(tmpbuf+strlen(AT_CIPSEND),buffer,2);

	//pc.puts(tmpbuf);pc.putc(0x0a);


	response = sendATCommand(tmpbuf,&SimcomRecBuf[0], "NONE");
	printSimcomBuffer(response);
	wait(0.5);

	sendSerialData(daatta, len);

}


