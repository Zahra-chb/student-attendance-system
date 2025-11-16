// ---------- GLOBAL VARIABLES ----------
let myChart;

// ---------- UPDATE ATTENDANCE ROW ----------
function updateAttendanceRow(row){
  const tds = row.querySelectorAll("td");
  const sessions = Array.from(tds).slice(2,8);
  const parts = Array.from(tds).slice(8,14);
  const absCell = tds[14];
  const partCell = tds[15];
  const msgCell = tds[16];

  const absCount = sessions.filter(td => td.textContent.trim()==="").length;
  const partCount = parts.filter(td => td.textContent.trim()==="✓").length;

  absCell.textContent = absCount;
  partCell.textContent = partCount;

  row.classList.remove("green","yellow","red");
  if(absCount < 3) { row.classList.add("green"); msgCell.textContent="Good attendance – Excellent participation"; }
  else if(absCount <=4){ row.classList.add("yellow"); msgCell.textContent="Warning – attendance low – You need to participate more"; }
  else{ row.classList.add("red"); msgCell.textContent="Excluded – too many absences – You need to participate more"; }

  // Notification
  const notif = document.getElementById("formMessage");
  notif.textContent = `Student: ${tds[1].textContent} ${tds[0].textContent}, Absences: ${absCount}, Participation: ${partCount}`;
}

// ---------- MAKE CELLS CLICKABLE ----------
function makeCellsInteractive(){
  document.querySelectorAll("#attendanceTable td[contenteditable='true']").forEach(td=>{
    td.addEventListener("click", e=>{
      td.textContent = td.textContent.trim()==="✓"?"":"✓"; // toggle ✓
      const row = td.parentElement;
      updateAttendanceRow(row);
      updatePieChart(); // update chart
    });
  });
}

// Initialisation
document.querySelectorAll("#attendanceTable tbody tr").forEach(row => updateAttendanceRow(row));
makeCellsInteractive();

// ---------- ADD STUDENT ----------
document.getElementById("studentForm").addEventListener("submit", e=>{
  e.preventDefault();
  const id = document.getElementById("studentId").value.trim();
  const ln = document.getElementById("lastName").value.trim();
  const fn = document.getElementById("firstName").value.trim();
  const em = document.getElementById("email").value.trim();
  const msgEl = document.getElementById("formMessage");

  if(!/^[0-9]+$/.test(id)) return msgEl.textContent="❌ Student ID must be numbers";
  if(!/^[A-Za-z]+$/.test(ln)) return msgEl.textContent="❌ Last name letters only";
  if(!/^[A-Za-z]+$/.test(fn)) return msgEl.textContent="❌ First name letters only";
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) return msgEl.textContent="❌ Invalid email";

  const tbody = document.querySelector("#attendanceTable tbody");
  const tr = document.createElement("tr");
  tr.innerHTML = `<td>${ln}</td><td>${fn}</td>${"<td contenteditable='true'></td>".repeat(12)}<td></td><td></td><td></td>`;
  tbody.appendChild(tr);
  makeCellsInteractive();
  updateAttendanceRow(tr);
  msgEl.textContent=`✅ Student added: ${fn} ${ln}`;
  e.target.reset();
  updatePieChart();
});

// ---------- PIE CHART ----------
function updatePieChart(){
  const rows = document.querySelectorAll("#attendanceTable tbody tr");
  const total = rows.length;
  const present = Array.from(rows).filter(r=>parseInt(r.children[14].textContent)<6).length;
  const participated = Array.from(rows).filter(r=>parseInt(r.children[15].textContent)>0).length;

  const ctx = document.getElementById('reportChart').getContext('2d');
  if(myChart) myChart.destroy();
  myChart = new Chart(ctx, {
    type:'pie',
    data:{
      labels:['Total Students','Present','Participated'],
      datasets:[{
        data:[total,present,participated],
        backgroundColor:['#007bff','#28a745','#ffc107'],
        borderColor:'#fff',
        borderWidth:2
      }]
    },
    options:{
      responsive:true,
      plugins:{legend:{display:true,position:'bottom'},title:{display:true,text:'Student Attendance Pie Chart'}}
    }
  });
}
document.querySelector('a[href="#report"]').addEventListener("click", updatePieChart);

// ---------- HOVER ----------
$(document).ready(function(){
  $("#attendanceTable tbody tr").hover(
    function(){ $(this).addClass("hovered"); },
    function(){ $(this).removeClass("hovered"); }
  );
});

// ---------- HIGHLIGHT EXCELLENT ----------
document.getElementById("excellentBtn").addEventListener("click",()=>{
  $("#attendanceTable tbody tr").each(function(){
    let absText = $(this).children().eq(14).text(); 
    let abs = parseInt(absText) || 0; // convertir en nombre, 0 si vide
    if(abs < 3) $(this).addClass("excellent");
  });
});

document.getElementById("resetBtn").addEventListener("click",()=>{
  $("#attendanceTable tbody tr").removeClass("excellent");
});

// ---------- SEARCH & SORT ----------
document.getElementById("searchInput").addEventListener("keyup",()=>{
  let value = searchInput.value.toLowerCase();
  $("#attendanceTable tbody tr").filter(function(){
    $(this).toggle($(this).text().toLowerCase().indexOf(value)>-1);
  });
});
document.getElementById("sortAbsAsc").addEventListener("click",()=>{
  let rows = $("#attendanceTable tbody tr").get();
  rows.sort((a,b)=>parseInt(a.children[14].textContent)-parseInt(b.children[14].textContent));
  $("#attendanceTable tbody").append(rows);
});
document.getElementById("sortParDesc").addEventListener("click",()=>{
  let rows = $("#attendanceTable tbody tr").get();
  rows.sort((a,b)=>parseInt(b.children[15].textContent)-parseInt(a.children[15].textContent));
  $("#attendanceTable tbody").append(rows);
});
