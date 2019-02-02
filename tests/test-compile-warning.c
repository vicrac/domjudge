/*
 * This should give compiler warnings and fail with NO-OUTPUT,WRONG-ANSWER
 *
 * @EXPECTED_RESULTS@: NO-OUTPUT,WRONG-ANSWER
 */

#include <stdio.h>

int main()
{
	char str[1000];

	gets(str);

	return 0;
}
