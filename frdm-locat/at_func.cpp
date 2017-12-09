/*
 * at_func.cpp
 *
 *  Created on: 8.12.2017
 *      Author: pauli
 */

#include "mbed.h"

#include "at_func.h"


extern Serial rfmodule;
extern Serial pc;

DigitalOut radioEnable(D2);

char str_in[50];

extern unsigned int SimcomRecPtr; 
extern unsigned char PcRecPtr;
extern char is_response;
extern char timer;

extern char SimcomRecBuf[];
extern void printSimcomBuffer(char ok);

int i = 0;
int at_rdy = 0, linefeeds = 0, carriages =0;
char s;

/*Function to send AT command string to module.
//input = address of string to be sent

First sends AT command, then waits for module response for couple of sec
if no response, return 0 */

uint8_t sendATCommand(char *command, char *answer, const char *expectedAnswer)
{

	if(rfmodule.readable())
	{
		while(rfmodule.readable())
		{
		char c = rfmodule.getc();
	}
	}


	char sendBuf[30];
	memset(sendBuf,0,sizeof(sendBuf));
	strcat(sendBuf,"AT+");

	strcat(sendBuf,command);
	uint8_t len = strlen(sendBuf);
	sendBuf[len]= 0x0d;

	is_response = 0;

	SimcomRecPtr = 0;

	

	pc.printf("Send to module: "); pc.puts(sendBuf); pc.putc(0x0a);
	rfmodule.puts(sendBuf);

	timer = 0;
	char *if_ok = 0;

	while(timer < 5 && !if_ok)
	{

		while(rfmodule.readable())
		{
			readATResponse(answer);
			is_response = 1;
			//wait(0.01);
		}

		
		if_ok = strstr(SimcomRecBuf,expectedAnswer);
		
	}

	if(if_ok)
	{
		is_response = 2;
	}

	return is_response;
}


uint8_t sendManualATCommand(char *command, char *answer)
{


if(rfmodule.readable())
	{
		while(rfmodule.readable())
		{
		char c = rfmodule.getc();
	}
	}
SimcomRecPtr = 0;
rfmodule.puts(command);
rfmodule.putc(0x0d);
timer = 0;
char *if_ok = 0;

	while(timer < 3 )
	{

		while(rfmodule.readable())
		{
			readATResponse(answer);
			is_response = 1;
			
		}

		//strcpy(tmpbuf,SimcomRecBuf);
		if_ok = strstr(SimcomRecBuf,"OK");
	}

	if(if_ok)
	{
		is_response = 2;
	}

	return is_response;

}


//Function to read SIMCOM module's response to AT command.
//input: address of response string buffer where response will be written

void readATResponse(char *answer)
{

	answer[SimcomRecPtr]= rfmodule.getc();

	SimcomRecPtr = (SimcomRecPtr+1) % 295;
	//timer = 0; 
	
}

uint8_t initSIMCOM(char *answer)
{

	unsigned char resp=0;

	pc.printf("Starting SIM928\r\n");

	radioEnable = 1;
	wait(0.5);

	while(resp != 2)
	{
	resp = sendManualATCommand("AT",answer);
	printSimcomBuffer(resp);
	
	}

	return resp;
}


void sendSerialData(const char *daatta)
{

	rfmodule.puts(daatta);
	rfmodule.putc(0x0d);

	SimcomRecPtr = 0;

	timer = 0;
	char *if_ok = 0;


	while(timer < 5 )
	{

		while(rfmodule.readable())
		{
			readATResponse(&SimcomRecBuf[0]);
			is_response = 1;
			
		}

		//strcpy(tmpbuf,SimcomRecBuf);
		if_ok = strstr(SimcomRecBuf,"FF");
	}

	if(if_ok)
	{
		is_response = 2;
	}

	printSimcomBuffer(is_response);
}