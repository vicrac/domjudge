/*
 * This will try to reboot the computer by calling 'reboot'. Of course
 * this should not work as this program should not run as root. This should
 * only generate a message to stderr, so result in NO-OUTPUT,WRONG-ANSWER.
 *
 * @EXPECTED_RESULTS@: NO-OUTPUT,WRONG-ANSWER
 */

#include <stdlib.h>

int main()
{
	return system("reboot");
}
