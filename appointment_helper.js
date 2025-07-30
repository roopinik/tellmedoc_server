import mysql from 'mysql2/promise';
import _, { map } from 'underscore';
import moment from 'moment';

const pool = mysql.createPool({
    host: '127.0.0.1',
    user: 'telmedocadmin',
    password: '5eSI7BJpjE0UjWV8ALVm',
    database: 'telmedoc2024',
    waitForConnections: true
})


export async function testConnection() {
    const connection = await pool.getConnection();
    var [rows] = await connection.query('select * from clients');
    connection.release(); // Release connection back to the pool
    return rows;
}

export async function getDepartments(clientId = 1) {
    const connection = await pool.getConnection();
    var [rows] = await connection.query(`SELECT s.id , s.description, name ->> '$.en' as title FROM specializations as s 
    inner join client_specialization as cs on cs.specialization_id = s.id
    WHERE cs.client_id =  ${clientId};`);
    connection.release(); // Release connection back to the pool
    return rows;
}

export async function getDoctors(clientId = 1, specialization = 1) {
    const connection = await pool.getConnection();
    var [rows] = await connection.query(`select d.id, d.name_translated ->> "$.en" as title,  CONCAT(YEAR(NOW()) - YEAR(d.working_since)," Years of Experience") AS description from filament_users as d 
inner join doctor_specialization as ds on ds.doctor_id = d.id
where d.client_id = ${clientId}
and ds.specialization_id = ${specialization};`);
    connection.release(); // Release connection back to the pool
    return rows;
}

export async function getAvailableDates(doctorid) {
    const connection = await pool.getConnection();
    var [rows] = await connection.query(`select d.id, d.appointment_slots as slots from filament_users as d 
right join clients as c on c.id = d.client_id
where d.id = ${doctorid}`); // Release connection back to the pool


    var slots = rows[0]['slots'];

    var days = slots.map(slot => slot['weekDay']);

    days = _.uniq(days);
    var today = new Date();
    var currentDay = today.toLocaleDateString('en-US', { weekday: 'long' });

    var availableDates = []

    if (days.includes(currentDay)) {
        availableDates.push(getFormattedDate(today))
    }

    for (var i = 1; i <= 15; i++) {
        today.setDate(today.getDate() + 1);
        var d = today.toLocaleDateString('en-US', { weekday: 'long' });
        if (days.includes(d)) {
            availableDates.push(getFormattedDate(today))
        }
    }


    connection.release();
    return availableDates;
}



function log(val) { console.log(val) }

function getFormattedDate(date) {
    const day = date.getDate();
    // Get month abbreviation
    const month = date.toLocaleString('en-US', { month: 'short' });

    return `${month} ${day}`;
}

// Function to get ordinal suffix (st, nd, rd, th)
function getOrdinalSuffix(day) {
    if (day > 3 && day < 21) return "th"; // 4th - 20th are always 'th'
    switch (day % 10) {
        case 1: return "st";
        case 2: return "nd";
        case 3: return "rd";
        default: return "th";
    }
}





