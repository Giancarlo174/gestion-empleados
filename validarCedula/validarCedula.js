// cedula.js:

/**
 * Validates a Panamenial id (Cédula)
 *
 * cedula - is a string
 *
 */
function validateCedula(cedula) {
    Array.prototype.insert = function(index, item) {
      this.splice(index, 0, item);
    };
  
    var re = /^P$|^(?:PE|E|N|[23456789]|[23456789](?:A|P)?|1[0123]?|1[0123]?(?:A|P)?)$|^(?:PE|E|N|[23456789]|[23456789](?:AV|PI)?|1[0123]?|1[0123]?(?:AV|PI)?)-?$|^(?:PE|E|N|[23456789](?:AV|PI)?|1[0123]?(?:AV|PI)?)-(?:\d{1,4})-?$|^(PE|E|N|[23456789](?:AV|PI)?|1[0123]?(?:AV|PI)?)-(\d{1,4})-(\d{1,6})$/i;
  
    var matched = cedula.match(re);
  
    // matched contains:
    // 1) if the cedula is complete (cedula = 8-AV-123-123)
    //    matched = [cedula, first part, second part, third part]
    //    [8AV-123-123]
    // 2) if the cedula is not complete (cedula = "1-1234")
    //    matched = ['1-1234', undefined, undefined, undefined]
    var isComplete = false;
  
    if (matched !== null) {
      matched.splice(0, 1); // remove the first match, it contains the input string.
  
      if (matched[0] !== undefined) {
        // if matched[0] is set => cedula complete
        isComplete = true;
  
        if (matched[0].match(/^PE|E|N$/)) {
          matched.insert(0, "0");
        }
  
        if (matched[0].match(/^(1[0123]?|[23456789])?$/)) {
          matched.insert(1, "");
        }
  
        if (matched[0].match(/^(1[0123]?|[23456789])(AV|PI)$/)) {
          var tmp = matched[0].match(/(\d+)(\w+)/);
  
          matched.splice(0, 1);
          matched.insert(0, tmp[1]);
          matched.insert(1, tmp[2]);
        }
      } // matched[0]
    }
  
    var result = {
      isValid: cedula.length === 0 ? true : re.test(cedula),
      inputString: cedula,
      isComplete: isComplete,
      cedula: isComplete ? matched.splice(0, 4) : null
    };
  
    return result;
}

// Exportar la función para uso en navegador
if (typeof module !== 'undefined' && module.exports) {
    module.exports.validate = validateCedula;
} else {
    // Para uso en navegador
    window.validateCedula = validateCedula;
}