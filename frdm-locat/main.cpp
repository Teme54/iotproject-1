#include "mbed.h"


#include "at_func.h"
//#include "at_commands.h"
#include "gps_func.h"
#include "gprs_func.h"
#include "locat_functions.h"

DigitalOut myled(LED1);

 
Serial pc(USBTX,USBRX,115200);
Serial rfmodule(D1,D0,57600);

Ticker kello;

unsigned int SimcomRecPtr = 0;
unsigned char PcRecPtr;
volatile char timer = 0;

char SimcomRecBuf[300];		//buffer for SIMCOM AT response message
char PcRecBuf[50];			//buffer for PC serial communication
char is_response = 1;


char client_id[20] = "LocatIoT-Node";
char topic[10] = "testi";
char msg[50] = "66.6-66.6";
uint8_t packet_len, trackingMode, sendData;


char mqtt_packet[100]; 

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
    char response = 0;
    //attach the receiveInt functions for both PC and SIMCOM uart interfaces
   // pc.attach(&PC_receiveInt, Serial::RxIrq);
    //rfmodule.attach(&RF_receiveInt, Serial::RxIrq);

    kello.attach(&timeoutTimer,0.1);	//attach the timeout timer

    response = initSIMCOM(&SimcomRecBuf[0]);
   
   	//UART3 ->C1 = 0xff;

   	parse_mqtt(topic,msg,strlen(topic),strlen(msg));

    GPRS_setPin("0000");
   

    initGPSTracking();

    GPRS_checkNetwork();
   
    GPRS_checkGPRSAttachment();
   
    GPRS_configureIP();
   
    GPRS_createTCPSocket();
   


    GPRS_sendData(mqtt_packet, packet_len);

    //pc.puts(mqtt_packet);
    pc.printf("MQTT packet sent\r\n");

    /*for(uint8_t p=0;p<packet_len;p++)
    {
    
    pc.printf("%x  ",mqtt_packet[p]);
    
    
    if(p>0 && (p%15 == 0))pc.printf("\r\n");
    
    }*/
    

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
        	timer = 0;

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


