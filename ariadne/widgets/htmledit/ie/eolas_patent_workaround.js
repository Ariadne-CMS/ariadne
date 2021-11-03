function CreateControl(DivID, CLSID, ObjectID, ObjectClass, params)
{
  var d = document.getElementById(DivID);
  var content = 
    '<object classid=' + CLSID + ' id=' + ObjectID + ' class=' + ObjectClass +' >\n';
  if (params) {
    for (var i in params) {
      content += '<param name="'+i+'" value="' + params[i] + '">\n'
    }
  }
  d.innerHTML = content;
}
