/*
 * at_func.h
 *
 *  Created on: 8.12.2017
 *      Author: pauli
 */

#ifndef AT_FUNC_H_
#define AT_FUNC_H_

#include "at_commands.h"

unsigned char sendATCommand(char *command, char *answer, const char *expectedAnswer);
void readATResponse(char *answer);
uint8_t initSIMCOM(char *answer);
uint8_t sendManualATCommand(char *command, char *answer);

void sendSerialData(const char *daatta);


#endif /* AT_FUNC_H_ */


