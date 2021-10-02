        <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
        <!-- Include TinyMCE -->
        <script src="<?php echo theme_path().'/assets/js/tinymce/jquery.tinymce.min.js' ?>"></script>
        <script src="<?php echo theme_path().'/assets/js/tinymce/tinymce.min.js' ?>"></script>
        <!-- End of Include TinyMCE -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
        <script src="<?php echo common_files().'/js/common.js'; ?>"></script>
        <script src="<?php echo theme_path().'/assets/project.js'; ?>"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                if(init.length){
                    for(var i in init){
                        init[i]();
                        }
                    }
                });
        </script>
    </body>
</html>