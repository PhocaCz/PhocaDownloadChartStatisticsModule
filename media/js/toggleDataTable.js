function transposeDataTable(e){for(var a=[],r=0;r<e.getNumberOfRows();r++){for(var u=[],o=0;o<e.getNumberOfColumns();o++)u.push(e.getValue(r,o));a.push(u)}var l=new google.visualization.DataTable;l.addColumn("string",e.getColumnLabel(0)),l.addRows(e.getNumberOfColumns()-1);o=1;for(var t=0;t<e.getNumberOfColumns()-1;t++){var n=e.getColumnLabel(o);l.setValue(t,0,n),o++}for(var s=0;s<a.length;s++){u=a[s];l.addColumn("number",u[0]);for(var g=0,m=1;m<u.length;m++)l.setValue(g,s+1,u[m]),g++}return l}