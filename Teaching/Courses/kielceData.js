/* SiteData */

/* The first part of this file is code that is under consideration for inclusion in Kielce */

Kielce.OldAssignment = (function() {
   "use strict";

   var makeDefaultLocation = function (title) {
      var title_noSpaces = title.replace(/\s+/g, "");
      var title_lowerCase = title_noSpaces.charAt(0).toLowerCase() + title_noSpaces.slice(1);

      return "Assignments/" + title_noSpaces + "/" + title_lowerCase + ".html";
   };

   var init = function(local_in, globalTitle_in, due_date_in, description_in, location_in) {

      var assignment = {
         localName: local_in,
         globalTitle: globalTitle_in,
         due_date: new Date(due_date_in),
         description: description_in || "",
         location: location_in || Kielce.Assignment.NONE
      };

      if (location_in === Kielce.Assignment.DEFAULT_LINK) {
         assignment.location = makeDefaultLocation(globalTitle_in);
      }

      assignment.fullTitle = function () {
         if (assignment.localName) {
            return assignment.localName + ": " + assignment.globalTitle;
         } else {
            return assignment.globalTitle;
         }
      };

      assignment.dateString = function (format) {
         var formatString = format ? format : 'DD, d MM';
         return assignment.due_date ? assignment.due_date.formattedDateString(formatString) : "";
      };

      return assignment;
   };

   var displayAssignments = function(assignments) {

       console.log("Displaying assignments", assignments);
      var table = "<table border=0 style='border-spacing: 35px 0'>\n";
      table += "<tr><th align='left'>Due</th><th align='left'>Name</th><th align='left'>Details</th></tr>\n";

       var sortedAssignments;
       if (typeof assignments.sort === "function") {
         sortedAssignments = assignments.sort(function (assignment) {
         console.log("Sort with", assignment);
         return assignment.due_date.valueOf();
       });
     } else {
	 sortedAssignments = [];
	 for (var key in assignments) {
	     if (assignments.hasOwnProperty(key)) {
		 sortedAssignments.push(assignments[key]);
             }
	 }
	 sortedAssignments = sortedAssignments.sort(function(a, b) {
	     return a.due_date.valueOf() - b.due_date.valueOf();
	 });
    }

     sortedAssignments.forEach(function(assignment) {
         var link = assignment.location === Kielce.Assignment.NONE ? assignment.fullTitle() : "<a href='" + assignment.location + "' >" + assignment.fullTitle() + "</a>";

         table += "<tr><td style='white-space: nowrap;'>" + assignment.dateString() + "</td><td style='white-space: nowrap;'>" + link + "</td><td>" + assignment.description + "</td></tr>\n";
      });
      table += "</table>\n";
      return table;
   };

   var dueDateMap = function (assignments) {
      var answer = {};
       console.log(assignments);
       if (typeof assignments.forEach === "function") {
      assignments.forEach( function (assignment, key) {
         answer[key] = assignment.dateString();
      });
       } else {
    for (var key in assignments) {
	if (assignments.hasOwnProperty(key)) {
	    console.log("Found key", key, assignments[key]);
	    answer[key] = assignments[key].dateString();
	}
}
}
      // log("DDM:", answer);
      return answer;
   };

   var makeLabList = function (labMap, weekZero) {
      var answer = "<table>";
      var labCount = 0;
      var makeLabLink = function (href) {
          labCount++;
          return "<a href='" + href + "'>Lab " + labCount + "</a>";
      };

      labMap.forEach(function (labInfo, week) {
               var labLink = "";
               var instructions = "";

               if (labInfo === null || labInfo === undefined) {
                  // do nothing
               } else if (typeof labInfo === "string") {
                    labLink = makeLabLink(labInfo);
               }
               else if (typeof labInfo === "object") {
                  if (labInfo.instructions) {
                     instructions = labInfo.instructions;
                  }
                  if (labInfo.message) {
                     labLink = labInfo.message;
                  }
                  if (labInfo.link) {
                     labLink = makeLabLink(labInfo.link);
                  }
               }
               answer += "<tr><td>" + weekZero.daysInFuture(7 * week).formattedDateString('d MM') + "</td><td>" + labLink + "</td></tr>";
            }
      )
            ;
      answer += "</table>";
      return answer;
   };

   return {
      init: init,
      displayAssignments: displayAssignments,
      dueDateMap: dueDateMap,
      makeLabList: makeLabList,
      DEFAULT_LINK: {},
      NONE: {}
   }
}());

if (typeof Kielce.Assignment === "undefined") {
       console.warn("Using OldAssignment");
    Kielce.Assignment = Kielce.OldAssignment;
}

({
   Default: {
      /* The following should be defined at other levels: courseName, courseTitle */

      // currentSemester here is used to color code the web pages so students don't accidentally
       // view incorrect information.
      currentSemester: 'S15',

      coursePageTitle: function (data) {
         return data.courseName + " " + data.courseTitle;
      },
      pageHeader:  function (data) {
         return "";
      },
      academicHonestyLink: 'http://www.cis.gvsu.edu/academic-honesty',
      cisJavaStyleGuideLink: 'http://www.cis.gvsu.edu/studentsupport/javaguide',
      secondaryAdmitLink: 'http://www.cis.gvsu.edu/academics/secondadmit',
      eosLabTutorialsLink: 'http://www.cis.gvsu.edu/Facilities/eosLabs/',
      siteBaseURL: function (data) {
         var answer = window.location.protocol + '//' + window.location.host + '/';
         if (window.location.host.indexOf("cis.gvsu.edu") > -1) {
            return answer + '~kurmasz/Teaching/Courses/';
         } else {
            return answer;
         }
      },
      courseBaseURL: function (data) {
         return data.siteBaseURL(data) + data.semesterCode + '/' + data.courseID + '/';
      },
      courseGeneralURL: function (data) {
         return data.siteBaseURL(data) + 'General/' + data.courseID + '/';
      },
      currentCourseURL: function (data) {
         return data.siteBaseURL(data) + data.currentSemester + '/' + data.courseID + '/';
      },

      instructorURL: "http://www.cis.gvsu.edu/~kurmasz",
	    instructorScheduleURL: "http://www.cis.gvsu.edu/public/staffListing/index.php?page=staff&fname=Zachary&lname=Kurmas",
	    //	    instructorScheduleURL: "http://www.cis.gvsu.edu/printschedule?fname=Zachary&lname=Kurmas",
      validator: '<a href="http://htmlhelp.com/cgi-bin/validate.cgi?url=referer"><img src="http://www.cis.gvsu.edu/~kurmasz/Teaching/Courses/html_validator.jpg" alt="WDG HTML Validation"></a>',
       validator5: '<a href="https://validator.w3.org/check?url=referer"><img src="http://www.cis.gvsu.edu/~kurmasz/Teaching/Courses/html5_validate_w3c.png" alt="W3c Validation" style="width: 100px;"></a>',


      // TODO:  Think about whether these functions are structured correctly, and
      // if they should be in site data.  For example, should we add stuff to Default
      // even before the first kielceData.js?  Also, should assignmentList be a new funtion,
      // or should assignmentList simply be set equal to the function Kielce.Assignment.displayAssignments
      // (then have  Kielce.Assignment.displayAssignments take data as a parameter instead of data.assignments)
      assignmentList: function(data) {
         return Kielce.Assignment.displayAssignments(data.assignments);
      },
       readingList: function(data) {
        return Kielce.Assignment.displayAssignments(data.readings);
       },
      due_dates: function(data) {
         return Kielce.Assignment.dueDateMap(data.assignments);
      },

       piazza: function(data) {
           return "http://piazza.com/gvsu/" + data.semesterName.toLowerCase().replace(/\s+/g, '') + "/" + data.courseID.toLowerCase();
       }

   },
   Override: {
      x: "y"
   }
})
