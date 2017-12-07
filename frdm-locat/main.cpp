#include "mbed.h"


#include "stdlib.h"


DigitalOut myled(LED1);
 
Serial pc(USBTX,USBRX,19200);

Serial rfmodule(D1,D0);

char str_in[100];
char rf_str[100];
unsigned char rf_pt;
char in_pt;
 
int main()
{
    int i = 0;
    int at_rdy = 0, linefeeds = 0;
    char s;
    pc.printf("Hello World!\n");
 
    while (true) {


    	if(pc.readable())

    	{
	        


	        do{
        	
        		if(pc.readable() >0)
        		{
		            s = pc.getc();
		            str_in[in_pt] = s;
		            in_pt++;
            	}
           	}
            while(s != 0x0d);

	        
        	in_pt++;
        	str_in[in_pt] = 0x00;
        	in_pt = 0;

        	pc.printf("received from PC:\r\n");
        	pc.puts(str_in); // print the value of variable i
        	pc.putc(0x0a);
        	at_rdy = 1;

	    }

	    if(at_rdy == 1)
	    {

	    	rfmodule.puts(str_in);
        	rfmodule.putc(0x0d);
        	pc.printf("String sent to module\r\n");

        	s = 0;

        	memset(str_in,0x00,sizeof(str_in));
        	at_rdy = 2;

	    }

        	
	    if(rfmodule.readable() && (at_rdy == 2))
	    {
	    	//s=rfmodule.getc();
	    	//pc.putc(s);

	    	rf_str[rf_pt] = rfmodule.getc();		
                if(rf_str[rf_pt] == 0x0a){ linefeeds++;}

			rf_pt++;

	    	if(linefeeds == 2){ 

	    		pc.printf("Got string from module\r\n");
	    		s = 0;

	    		rf_pt++;
	        	rf_str[rf_pt] = 0x00;
	        	rf_pt = 0;
	        	
	        	pc.printf("received from module:\r\n");
	        	pc.puts(rf_str); // print the value of variable i
	        	pc.putc(0x0a);

	        	at_rdy = 0;
	        	linefeeds = 0;
	        	s = 0;

	        	memset(rf_str,0x00,sizeof(rf_str));

	    	}
	    	
	    	//pc.putc();

	    	
        }
        
      
    }
}