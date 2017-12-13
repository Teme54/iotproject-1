#include "mbed.h"
#include "FXOS8700Q.h"
#define FXOS8700Q_CTRL_REG4 0x2D
#define FXOS8700Q_CTRL_REG5 0x2E
#define FXOS8700Q_A_FFMT_CFG 0x15
#define FXOS8700Q_A_FFMT_THS 0x17
#define FXOS8700Q_CTRL_REG3 0x2C

//I2C lines for FXOS8700Q accelerometer/magnetometer
FXOS8700Q_acc acc(PTE25, PTE24, FXOS8700CQ_SLAVE_ADDR1);
DigitalOut Red(LED1);
InterruptIn inter(PTC13);

//InterruptIn Interrupt(SW2);

//Temrinal enable
Serial pc(USBTX, USBRX);

MotionSensorDataUnits acc_data;

float faX, faY, faZ;

void interrupt2() {

	uint8_t testi[2];

	acc.readRegs(FXOS8700Q_CTRL_REG4, &testi[1], 1);
	testi[1] |= 0x84;
	testi[0] = FXOS8700Q_CTRL_REG4;
	acc.writeRegs(testi, 2);
	acc.readRegs(FXOS8700Q_CTRL_REG4, &testi[1], 1);

	acc.readRegs( FXOS8700Q_CTRL_REG5, &testi[1], 1);
	testi[1] &= ~0x04;
	testi[0] = FXOS8700Q_CTRL_REG5;
	acc.writeRegs(testi, 2);
	acc.readRegs( FXOS8700Q_CTRL_REG5, &testi[1], 1);

	acc.readRegs( FXOS8700Q_CTRL_REG3, &testi[1], 1);
		testi[1] |= 0x08;
		testi[0] = FXOS8700Q_CTRL_REG3;
		acc.writeRegs(testi, 2);
		acc.readRegs( FXOS8700Q_CTRL_REG3, &testi[1], 1);

	acc.readRegs( FXOS8700Q_A_FFMT_CFG, &testi[1], 1);
	testi[1] |= 0x78;
	testi[0] = FXOS8700Q_A_FFMT_CFG;
	acc.writeRegs(testi, 2);

	acc.readRegs( FXOS8700Q_A_FFMT_THS, &testi[1], 1);
	testi[1] |= 0x8F;
	testi[0] = FXOS8700Q_A_FFMT_THS;
	acc.writeRegs(testi, 2);


}
void keskeytys() {
	pc.printf("\r\n\nFXOS8700Q Toimii\r\n");
}

int main() {
	//float faX, faY, faZ;
	//virransäästö rekistereiden alustus
	//SMC ->PMPROT |= 0x20;
	//SMC ->PMCTRL |= 0x40;
	//MCG ->C6 &= ~0x20;

	//uint8_t powermode , test;
	//powermode = SMC ->PMPROT;
	//test = SMC ->PMCTRL;

	pc.printf("\r\n\nFXOS8700Q eoprgmiosmoigmiod\r\n");
	interrupt2();
	acc.enable();

	inter.fall(&keskeytys);

	while (true) {



		Red = !Red;


		wait(3.0);
	}
}
