
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const steps = document.querySelectorAll('.step');
        steps.forEach(step => {
            step.style.display = 'none';  
        });
        const currentStep = <?php echo $_SESSION['step']; ?>;
        const currentStepElement = document.getElementById('step' + currentStep);
        if (currentStepElement) {
            currentStepElement.style.display = 'block';
        }
    });
    
    document.addEventListener("DOMContentLoaded", function() {
        let page = 1;
        console.log(page)
        document.getElementById("loadMoreBtn").addEventListener("click", function() {
            page++;
            fetch(`../scripts/searchSetlists.php?p=${page}`)
                .then(response => {
                    if (response.ok) {
                        window.location.href = "../index.php";
                    } else {
                        console.error("Failed to fetch more results.");
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });

    document.getElementById('privacySwitch').addEventListener('change', function() {
        var label = document.getElementById('privacyLabel');
        if (this.checked) {
            label.textContent = 'Private'; 
        } else {
            label.textContent = 'Public';  
        }
    });

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
