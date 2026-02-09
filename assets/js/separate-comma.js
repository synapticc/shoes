// assets/js/separate-comma.js


/* Adds a comma in number in case JS in-built functions fails.
 Taken from 'https://www.tutorialstonight.com/javascript-number-format.php#decimal' */
export default function separateComma(val)
{
  // remove sign if negative
  var sign = 1;
  if (val < 0) {
    sign = -1;
    val = -val;
  }
  // trim the number decimal point if it exists
  let num = val.toString().includes('.') ? val.toString().split('.')[0] : val.toString();
  let len = num.toString().length;
  let result = '';
  let count = 1;

  for (let i = len - 1; i >= 0; i--) {
    result = num.toString()[i] + result;
    if (count % 3 === 0 && count !== 0 && i !== 0) {
      result = ',' + result;
    }
    count++;
  }

  // add number after decimal point
  if (val.toString().includes('.')) {
    result = result + '.' + val.toString().split('.')[1];
  }
  // return result with - sign if negative
  return sign < 0 ? '-' + result : result;
}
