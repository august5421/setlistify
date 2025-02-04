async function searchSetlists() {
    artist = document.getElementById("artistInput").value.trim();
    venue = document.getElementById("venueInput").value.trim();
    tour = document.getElementById("tourInput").value.trim();
    year = document.getElementById("yearInput").value.trim(); 

    currentPage = 1; 

    const params = new URLSearchParams();
    if (artist) params.append("artist", artist);
    if (venue) params.append("venue", venue);
    if (tour) params.append("tour", tour);
    if (year) params.append("year", year);

    try {
        const response = await fetch(`searchSetlists.php?${params.toString()}&p=${currentPage}`);
        const data = await response.json();
        displayResults(data.setlist);
    } catch (error) {
        console.error("Error fetching setlists:", error);
    }
}

function displayResults(setlists, append = false) {
    const resultsDiv = document.getElementById("searchResults");

    if (!append) {
        resultsDiv.innerHTML = "";
    }

    if (!setlists || setlists.length === 0) {
        if (!append) {
            resultsDiv.innerHTML = "<p>No setlists found.</p>";
        }
        loadMoreButton.style.display = "none";
        return;
    }

    setlists.forEach(setlist => {
        const div = document.createElement("div");
        div.classList.add("setlist");
        div.dataset.setlistId = setlist.id; 

        const formattedDate = formatDate(setlist.eventDate);

        div.innerHTML = `
            <h2 class="purpCol">${setlist.artist.name}</h2>
            <h3>${formattedDate}</h3>
            <p><strong>Venue:</strong> ${setlist.venue.name}, ${setlist.venue.city.name}</p>
            ${setlist.tour ? `<p><strong>Tour:</strong> ${setlist.tour.name}</p>` : ""}
        `;

        div.addEventListener("click", () => fetchSetlistDetails(setlist.id)); 
        resultsDiv.appendChild(div);
    });

    loadMoreButton.style.display = "flex";
}