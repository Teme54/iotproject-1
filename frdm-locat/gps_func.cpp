#include "mbed.h"

#include "gps_func.h"


/* 
Functions for GPS management


locateGPS();

initGPSTracking();

*/

extern char SimcomRecBuf[];
extern void printSimcomBuffer(char ok);
extern uint8_t sendATCommand(char *command, char *answer,const char *expectedAnswer);

extern Serial pc;


void locateGPS()
{

	char response;
	response = sendATCommand(&AT_GPSINFO[0], &SimcomRecBuf[0], "OK");
	printSimcomBuffer(response);

}



void initGPSTracking()

{

	char *if_ok = 0;
	for(uint8_t tries = 0; tries < 6; tries++)
	{
		char response;
		response = sendATCommand(&AT_GPSDBG_ON[0], &SimcomRecBuf[0], "OK");

		if(response == 2)
		{
			
			tries = 6;
			
		}
		else
		{
			pc.printf("Tries: %d \r\n",tries);
		}

		printSimcomBuffer(response);
		



		

	}

}


