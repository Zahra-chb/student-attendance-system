function updateAttendance() {
  const rows = document.querySelectorAll("#attendanceTable tbody tr");
  rows.forEach(row => {
    row.classList.remove("green","yellow","red");
    const tds = row.querySelectorAll("td");
    const sessions = Array.from(tds).slice(2,8);
    const parts = Array.from(tds).slice(8,14);
    const abs = tds[14], part = tds[15], msg = tds[16];
    const absCount = sessions.filter(c => c.textContent.trim()==="").length;
    const partCount = parts.filter(c => c.textContent.trim()==="✓").length;
    abs.textContent = `${absCount} Abs`;
    part.textContent = `${partCount} Par`;

    if(absCount<3){
      row.classList.add("green");
      msg.textContent="Good attendance – Excellent participation";
    } else if(absCount<=4){
      row.classList.add("yellow");
      msg.textContent="Warning – attendance low";
    } else {
      row.classList.add("red");
      msg.textContent="Excluded – too many absences";
    }
  });
}

updateAttendance();
document.getElementById("updateBtn").addEventListener("click", updateAttendance);

// ---------- Add Student ----------
document.getElementById("studentForm").addEventListener("submit", e => {
  e.preventDefault();
  const id = document.getElementById("studentId").value.trim();
  const ln = document.getElementById("lastName").value.trim();
  const fn = document.getElementById("firstName").value.trim();
  const em = document.getElementById("email").value.trim();
  const msg = document.getElementById("formMessage");

  if(!/^[0-9]+$/.test(id)) return msg.textContent = "❌ Student ID: numbers only";
  if(!/^[A-Za-z]+$/.test(ln)) return msg.textContent = "❌ Last name: letters only";
  if(!/^[A-Za-z]+$/.test(fn)) return msg.textContent = "❌ First name: letters only";

  // ✅ FIXED email regex — now correct and not too strict
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em))
    return msg.textContent = "❌ Invalid email";

  const tbody = document.querySelector("#attendanceTable tbody");
  const tr = document.createElement("tr");
  tr.innerHTML = `<td>${ln}</td><td>${fn}</td>
    ${"<td contenteditable='true'></td>".repeat(12)}
    <td></td><td></td><td></td>`;
  tbody.appendChild(tr);
  updateAttendance();
  msg.textContent = "✅ Student added successfully!";
  e.target.reset();
});

// ---------- Simple Report ----------
function showReport(){
  const rows = document.querySelectorAll("#attendanceTable tbody tr");
  const total = rows.length;
  const good = Array.from(rows).filter(r => r.classList.contains("green")).length;
  const yellow = Array.from(rows).filter(r => r.classList.contains("yellow")).length;
  const red = Array.from(rows).filter(r => r.classList.contains("red")).length;
  document.getElementById("reportData").innerHTML =
    `<p><b>Total students:</b> ${total}</p>
     <p style="color:#198754"><b>Good:</b> ${good}</p>
     <p style="color:#ffc107"><b>Warning:</b> ${yellow}</p>
     <p style="color:#dc3545"><b>Excluded:</b> ${red}</p>`;
}

document.querySelector('a[href="#report"]').addEventListener("click", showReport);
