<?php
include('includes/navbar.php');
?>

<div class="container mt-5 mainContain">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="col-lg-12 mwMax">
            <?php if ($_SESSION['step'] == 1): ?>
                <div id="step1" class="step">
                    <form action="/scripts/searchSetlists.php" method="get">
                        <div class="display-6 mb-3">Search For A Setlist</div>
                        <div class="row justify-content-center align-items-center">
                            <div class="col mb-3">
                                <input type="text" id="artistInput" name="artist" class="form-control" placeholder="Enter artist name">
                            </div>
                            <div class="row mb-3">
                                <input type="text" id="venueInput" name="venue" class="form-control" placeholder="Enter venue name">
                            </div>
                            <div class="row mb-3">
                                <input type="text" id="tourInput" name="tour" class="form-control" placeholder="Enter tour name">
                            </div>
                            <div class="row mb-3">
                                <input type="text" id="yearInput" name="year" class="form-control" placeholder="Enter year (e.g., 2023)" maxlength="4">
                            </div>
                            <div class="row mb-3 d-flex justify-content-center align-items-center">
                                <button type="submit" class="btn btn-info w-100">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            <?php if ($_SESSION['step'] == 2): ?>
                <div id="step2" class="step">
                    <div id="loadingResults" style="display: none;">
                        <div class="loading-container d-flex flex-column">
                            <div class="loading-spinner"></div>
                            <p>Please wait while we prepare everything for you...</p>
                        </div>
                    </div>
                    <div id="searchResults">
                        <div class="d-flex flex-column">
                            <?php
                            if (isset($_SESSION['searchResults'])):
                                $searchResults = $_SESSION['searchResults'];
                                if (isset($searchResults['error'])): ?>
                                    <p><?php echo $searchResults['error']; ?></p>
                                <?php else: ?>
                                    
                                    <?php foreach ($searchResults['setlist'] as $setlist): ?>
                                        <div class="d-flex row">
                                            <div class="setlist-card p-2 border">
                                                <div class="d-flex flex-row">
                                                    <div class="d-flex flex-column me-4">
                                                        <div class="date-box">
                                                            <?php
                                                                $eventDate = strtotime($setlist['eventDate']);
                                                                $month = date('M', $eventDate);
                                                                $day = date('d', $eventDate); 
                                                                $year = date('Y', $eventDate); 
                                                            ?>
                                                            <div class="lightText"><?php echo strtolower($month); ?></div>
                                                            <div class="lightText display-6"><?php echo $day; ?></div>
                                                            <div class="lightText"><?php echo $year; ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column flex-grow-1 justify-content-around">
                                                        <div class="primaryText display-6"><?php echo $setlist['artist']['name']; ?> Live At <?php echo $setlist['venue']['name']; ?></div>
                                                        <div class="d-flex flex-row ">
                                                            <div class="d-flex flex-column me-4">
                                                                <strong class="auxilaryText">Artist:</strong> <?php echo $setlist['artist']['name']; ?>
                                                            </div>
                                                            <div class="d-flex flex-column me-4">
                                                                <strong class="auxilaryText">Venue:</strong> <?php echo $setlist['venue']['name']; ?>
                                                            </div>
                                                            <?php if (isset($setlist['tour']['name'])): ?>
                                                                <div class="d-flex flex-column">
                                                                    <strong class="auxilaryText">Tour:</strong> <?php echo $setlist['tour']['name']; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column me-4 justify-content-center">
                                                        <form action="scripts/fetchSetlistDetails.php" method="GET">
                                                            <input type="hidden" name="setlistId" value="<?php echo $setlist['id']; ?>">
                                                            <input type="hidden" name="setlistArtist" value="<?php echo $setlist['artist']['name']; ?>">
                                                            <button class="btn btn-info view-setlist" type="submit" onclick="showLoad()">Show Setlist</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($_SESSION['step'] == 3): ?>
                <div id="step3" class="mwMax d-flex flex-column flex-lg-row">

                <?php
                if (isset($_SESSION['setlistDetails']) && !isset($_SESSION['setlistDetails']['error'])):
                    $setlistDetails = $_SESSION['setlistDetails'];
                    $trackDetails = $_SESSION['trackDetails'];
                    $matchedTracks = array_filter($trackDetails, function($track) {
                        return $track['artist'] !== 'Unknown';
                    });
                    $unmatchedTracks = array_map(function($track, $index) {
                        return [
                            'index' => $index,
                            'song_name' => $track['title'], 
                            'track' => $track
                        ];
                    }, $trackDetails, array_keys($trackDetails));
                    $unmatchedTracks = array_filter($unmatchedTracks, function($track) {
                        return $track['track']['artist'] == 'Unknown';
                    });
                    $matchedTrackCount = count($matchedTracks);
                    $unmatchedTrackCount = count($unmatchedTracks);
                    $trackCount = count($trackDetails);
                    $brokenArray = array();
                    foreach ($unmatchedTracks as $key => $song) {
                        $brokenArray[] = array(
                            'title' => $song['track']['title'],
                            'index' => $song['index']
                        );
                    }

                ?>
                    
                        <div class="d-flex flex-column flex-grow-1 me-0 me-lg-4 col-12 col-lg-6">
                            <img class="mb-3" width="300px" src="scripts/imageModifier.php?text=<?php echo urlencode($setlistDetails['artist']['name'] . ' Live At ' . $setlistDetails['venue']['name'] . ' (' . date('M d, Y', strtotime($setlistDetails['eventDate'])) . ')'); ?>" alt="Artist Image" />
                            <div class="input-group input-group-lg">
                                <span class="input-group-text" id="inputGroup-sizing-lg">Playlist Name:</span>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="userInputForNam"
                                    aria-label="Sizing example input" 
                                    aria-describedby="inputGroup-sizing-lg"
                                    value="<?php echo $setlistDetails['artist']['name']; ?> Live At <?php echo $setlistDetails['venue']['name']; ?> (<?php echo date('M d, Y', strtotime($setlistDetails['eventDate'])); ?>)"
                                >
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="privacySwitch" checked>
                                <label class="form-check-label" id="privacyLabel" for="privacySwitch">Private</label>
                            </div>
                            <div class="mt-2">
                                <p><strong class="auxilaryText">Total Playlist Duration: </strong><?php echo $_SESSION['totalDuration']; ?></p>
                                <p><strong class="auxilaryText">Tracks Matched: </strong><?php echo $matchedTrackCount; ?></p>
                                <?php if ($unmatchedTrackCount > 0): ?>
                                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                            <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                            </symbol>
                                            <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                                            </symbol>
                                            <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                            </symbol>
                                        </svg>
                                        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
                                        <div class="d-flex flex-column w-100">
                                            We were unable to find spotify tracks for <?php echo $unmatchedTrackCount; ?> of the tracks from the setlist. 
                                            <?php 
                                                if ($unmatchedTrackCount > 1) {
                                                    echo 'The following tracks were not found:'; 
                                                } else {
                                                    echo 'The following track was not found:';
                                                }
                                            ?>
                                            <?php 
                                                foreach ($brokenArray as $song) {
                                                    if ($song === end($brokenArray)) {
                                                        if ($unmatchedTrackCount > 1) {
                                                            echo 'and ' .$song['title'];
                                                        } else {
                                                            echo $song['title'];
                                                        }
                                                    } else {
                                                        echo $song['title'];
                                                    }
                                                    echo "<br>";
                                                }
                                            ?>
                                        </div>
                                    </div>
                                    
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex flex-column oFlowScro mwMax flex-grow-1 col-12 col-lg-6">
                            <div class="d-flex flex-column">
                                <?php foreach ($trackDetails as $track): ?>
                                    <?php if ($track['artist'] !== 'Unknown'): ?>
                                        <div class="d-flex flex-row mb-2">
                                            <div class="d-flex flex-row">
                                                <?php if (isset($track['coverArt'])): ?>
                                                    <img src="<?php echo $track['coverArt']; ?>" alt="Cover Art" style="width: 75px; height: 75px; margin-right: 15px;">
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex flex-row flex-grow-1">
                                                <div class="d-flex flex-column justify-content-around">
                                                <div class="primaryText display-6">
                                                    <?php
                                                        $title = htmlspecialchars($track['title']);
                                                        if (mb_strlen($title) > 50) {
                                                            echo mb_substr($title, 0, 47) . '...';
                                                        } else {
                                                            echo $title;
                                                        }
                                                    ?>
                                                </div>

                                                    <div class="d-flex flex-column flex-lg-row">
                                                        <div class="d-flex flex-row">
                                                            <div class="d-flex flex-column me-2 auxilaryText">Artist: </div>
                                                            <div class="d-flex flex-column me-4">
                                                                <?php $trackArt = htmlspecialchars($track['artist']);
                                                                if (mb_strlen($trackArt) > 35) {
                                                                    echo mb_substr($trackArt, 0, 32) . '...';
                                                                } else {
                                                                    echo $trackArt;
                                                                } ?>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-row">
                                                            <div class="d-flex flex-column me-2 auxilaryText">Album: </div>
                                                            <div class="d-flex flex-column me-4">
                                                                <?php $trackAlb = htmlspecialchars($track['album']);
                                                                if (mb_strlen($trackAlb) > 35) {
                                                                    echo mb_substr($trackAlb, 0, 32) . '...';
                                                                } else {
                                                                    echo $trackAlb;
                                                                } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>Error: <?php echo $_SESSION['setlistDetails']['error']; ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($_SESSION['step'] == 4): ?>
                <div id="step4" class="step">
                    <?php if (isset($_SESSION['playlistError'])): ?>
                        <div class="primaryText display-6 mb-3">Oops!</div>
                        <div class="alert alert-danger" role="alert">
                            We seem to be having trouble creating this playlist. Please try a different setlist.
                        </div>
                        <?php if (isset($_SESSION['playlistID'])): ?>
                            <div class="d-flex flex-row">
                                <div class='d-flex flex-column'>
                                    <form action="scripts/nullify.php" method="POST">
                                        <button type="submit" class="btn btn-secondary mt-3 me-3">Try another setlist</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="primaryText display-6 mb-3">Success!</div>
                        <div class="alert alert-success" role="alert">
                            Your playlist has been successfully submitted!
                        </div>
                        <?php if (isset($_SESSION['playlistID'])): ?>
                            <div class="d-flex flex-row">
                                <div class='d-flex flex-column'>
                                    <form action="scripts/nullify.php" method="POST">
                                        <button type="submit" class="btn btn-secondary mt-3 me-3">Create another playlist</button>
                                    </form>
                                </div>
                                <div class='d-flex flex-column'>
                                    <a href="https://open.spotify.com/playlist/<?php echo $_SESSION['playlistID']; ?>" target="_blank">
                                        <button class="btn btn-info mt-3">View Playlist on Spotify</button>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<footer class="bg-dark mt-5">
    <div class="d-flex justify-content-center">
        <div class="innerFoot">
            <?php if ($_SESSION['step'] == 2): ?>
                <a href="../scripts/resetSearch.php?next=<?php echo $_SESSION['step'] - 1; ?>" class="btn btn-secondary">Back to Search</a>
                <?php if (count($searchResults['setlist']) >= 20): ?>
                    <a id="loadMoreBtn" class="btn btn-info">Load More</a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($_SESSION['step'] == 3): ?>
                <a href="index.php?next=<?php echo $_SESSION['step'] - 1; ?>" class="btn btn-secondary">Back to Search Results</a>
                
                <?php if (isset($_SESSION['spotifyAccessToken']) && !empty($_SESSION['spotifyAccessToken'])): ?>
                    <form action="scripts/createSpotifyPlaylist.php" method="POST">
                        <input type="hidden" name="playlistName" id="userInputForNamHid" value="<?php echo $setlistDetails['artist']['name']; ?> Live At <?php echo $setlistDetails['venue']['name']; ?> (<?php echo date('M d, Y', strtotime($setlistDetails['eventDate'])); ?>)">
                        <input type="hidden" name="playlistSongs" value="<?php echo htmlspecialchars(json_encode($matchedTracks)); ?>">
                        <input type="hidden" name="privacySwitchHidden" id="privacySwitchHidden" value="private">
                        <input type="hidden" name="playlistImage" id="playlistImage" value="">
                        <button class="btn btn-info view-setlist" type="submit">Save Playlist</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-info" href="<?php echo $_SESSION['authUrl']; ?>" role="button">Login with Spotify</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</footer>
<script>
    const playlistNameInput = document.getElementById("userInputForNam");
    const playlistNameHidden = document.getElementById("userInputForNamHid");
    const playlistPrivacy = document.getElementById("privacySwitch");
    const playlistPrivacyHidden = document.getElementById("privacySwitchHidden");
    const searchResultsDiv = document.getElementById("searchResults");
    const loadingResultsDiv = document.getElementById("loadingResults");
    playlistNameInput.addEventListener("input", (event) => {
        console.log('ikjsbrfg')
        if (playlistNameInput && playlistNameHidden) {
            playlistNameHidden.value = playlistNameInput.value;
        }
    });
    playlistPrivacy.addEventListener("input", () => {
        playlistPrivacyHidden.value = playlistPrivacy.checked ? "private" : "public";
    });
    document.querySelector("form").addEventListener("submit", function(event) {
        event.preventDefault(); 

        const textParam = encodeURIComponent(document.getElementById('userInputForNamHid').value);
        fetch('scripts/imageModifier.php?text=' + textParam + '&base64=true')
            .then(response => response.json())
            .then(data => {
                document.getElementById('playlistImage').value = data.image; 
                event.target.submit(); 
            })
            .catch(error => console.error('Error fetching image:', error));
    });
    function showLoad() {
        searchResultsDiv.style.display = "none";
        loadingResultsDiv.style.display = "block";
    }
</script>
<?php
include('includes/footer.php');
?>
