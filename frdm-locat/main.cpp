#include "mbed.h"


#include "at_func.h"
//#include "at_commands.h"
#include "gps_func.h"
#include "gprs_func.h"

DigitalOut myled(LED1);

 
Serial pc(USBTX,USBRX,115200);
Serial rfmodule(D1,D0,9600);

Ticker kello;

unsigned int SimcomRecPtr = 0;
unsigned char PcRecPtr;
volatile char timer = 0;

char SimcomRecBuf[300];		//buffer for SIMCOM AT response message
char PcRecBuf[50];			//buffer for PC serial communication
char is_response = 1;

void PC_receiveInt();
void RF_receiveInt();

void timeoutTimer()
{
	timer++;
}


/* Print the SIMCOM response buffer to PC if ok is 1. Else print a fail message*/

void printSimcomBuffer(char ok)
{
	if (ok)
	{

		pc.printf("received from module:\r\n");
		pc.puts(SimcomRecBuf); // print the value of variable i
		pc.printf("\r\n");

		memset(SimcomRecBuf,0x00,strlen(SimcomRecBuf));

		SimcomRecPtr = 0;
	}
	else
	{
		pc.printf("No response from module! \r\n");
		memset(SimcomRecBuf,0x00,strlen(SimcomRecBuf));
	}
}
 
int main()
{
    
    pc.printf("LocatIoT started\r\n");

    char s = 0;
    char waiting = 0;
    char response = 0;
    //attach the receiveInt functions for both PC and SIMCOM uart interfaces
   // pc.attach(&PC_receiveInt, Serial::RxIrq);
    //rfmodule.attach(&RF_receiveInt, Serial::RxIrq);

    kello.attach(&timeoutTimer,0.5);	//attach the timeout timer

    response = initSIMCOM(&SimcomRecBuf[0]);
   
   	//UART3 ->C1 = 0xff;

    GPRS_setPin("0000");
    wait(0.5);

    initGPSTracking();
    wait(0.5);
    GPRS_checkNetwork();
    wait(0.5);
    GPRS_checkGPRSAttachment();
    wait(0.5);
    GPRS_configureIP();
    wait(1);
    GPRS_createTCPSocket();
    wait(2);

    GPRS_sendData();
    

    while (true) {

    	if(pc.readable())
    	{
	        
	        do{
        		if(pc.readable() >0)
        		{
		            s = pc.getc();
		            pc.putc(s);
		            PcRecBuf[PcRecPtr] = s;
		            PcRecPtr++;
            	}            	
           	}
            while(s != 0x0d);
        	

        	pc.printf("received from PC:\r\n");
        	pc.puts(PcRecBuf); // print the value of variable i
        	pc.putc(0x0a);
        	
        	pc.printf("String sent to module\r\n\r\n");

        	s = 0;
        	response = sendManualATCommand(&PcRecBuf[0], &SimcomRecBuf[0]);
        	printSimcomBuffer(response);

        	memset(PcRecBuf,0x00,sizeof(PcRecBuf));

        	PcRecPtr = 0;
        	waiting = 1;timer = 0;

	    }

    	
    	//locateGPS();
    
    }
}



void RF_receiveInt() //if there is incoming character from SIMCOM
{

	/*while(rfmodule.readable()) 
	{

		char c = rfmodule.getc(); //get the char
		rf_str[rf_pt] = c; //add it to the rf module response string
		rf_pt++; //increment string pointer

		if(c == 0x0a) linefeeds++;

		if(linefeeds > 1) rfReceiveRdy = 1; //if there is 2 LF then SIMCOM has finished transmitting, so we are ready

	}*/
}


