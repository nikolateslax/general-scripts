function implode(str, sep) {
  let c = 0;
  let s = "";
  while ((c++) < str.length) {
    s = s + str[c];
  }
  return s;
}
