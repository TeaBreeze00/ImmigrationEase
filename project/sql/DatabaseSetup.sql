/*
DROP STATEMENTS
*/
DROP TABLE WillLiveAt;
DROP TABLE WillStudyAt;
DROP TABLE GraduatedFrom;
DROP TABLE WillWorkAt;
DROP TABLE Has;
DROP TABLE HasAccount;
DROP TABLE Applies;
DROP TABLE BIDToLocation;
DROP TABLE IRCC;
DROP TABLE Bank;
DROP TABLE House;
DROP TABLE CriminalHistory;
DROP TABLE HealthHistory;
DROP TABLE History;
DROP TABLE InternationalSchool;
DROP TABLE School;
DROP TABLE Workplace;
DROP TABLE Student;
DROP TABLE Worker;
DROP TABLE Immigrant;

/*
ENTITY TABLES
*/

CREATE TABLE Immigrant (
    PassportNumber CHAR(8) NOT NULL,
    Name VARCHAR(20),
    DOB CHAR(10),
    Gender CHAR(1),
    Email VARCHAR(255),
    Passcode VARCHAR(255),
    userType CHAR(1) DEFAULT 'R',
    CONSTRAINT pk_Immigrant  PRIMARY KEY (PassportNumber),
    CONSTRAINT c_DOB CHECK (DOB LIKE '____-__-__'),
    CONSTRAINT c_userType CHECK (userType IN ('R', 'A'))
);

CREATE TABLE Student (
    PassportNumber CHAR(8) NOT NULL,
    StudentNumber CHAR(5) NOT NULL,
    CONSTRAINT pk_Student PRIMARY KEY (PassportNumber),
    CONSTRAINT fk_Student_Immigrant FOREIGN KEY (PassportNumber) REFERENCES Immigrant(PassportNumber) ON DELETE CASCADE
);

CREATE TABLE Worker (
    PassportNumber CHAR(8) NOT NULL,
    CONSTRAINT pk_Worker PRIMARY KEY (PassportNumber),
    CONSTRAINT fk_Worker_Immigrant FOREIGN KEY (PassportNumber) REFERENCES Immigrant(PassportNumber) ON DELETE CASCADE
);

CREATE TABLE Workplace (
    BusinessNumber CHAR(10) NOT NULL,
    Name VARCHAR(20),
    Location VARCHAR(255),
    CONSTRAINT pk_Workplace PRIMARY KEY (BusinessNumber)
);

CREATE TABLE School (
    DLI CHAR(10) NOT NULL,
    Location VARCHAR(255),
    Name VARCHAR(20),
    CONSTRAINT pk_School PRIMARY KEY (DLI)
);

CREATE TABLE InternationalSchool (
    Name VARCHAR(255) NOT NULL,
    Location VARCHAR(255),
    CONSTRAINT pk_ISchool PRIMARY KEY (Name)
);

CREATE TABLE History (
    FileNumber CHAR(15) NOT NULL,
    DateOfEvent CHAR(10),
    ExpiryDate CHAR(10),
    CertifyingInstitution VARCHAR(20),
    CONSTRAINT pk_History PRIMARY KEY (FileNumber),
    CONSTRAINT c_doe CHECK (DateOfEvent LIKE '____-__-__'),
    CONSTRAINT c_ed CHECK (ExpiryDate LIKE '____-__-__')
);

CREATE TABLE HealthHistory (
    FileNumber CHAR(15) NOT NULL,
    Type VARCHAR(20),
    Description VARCHAR(255),
    CONSTRAINT pk_HealthHistory PRIMARY KEY (FileNumber),
    CONSTRAINT fk_HealthHistory_History FOREIGN KEY (FileNumber) REFERENCES History(FileNumber) ON DELETE CASCADE,
    CONSTRAINT c_HealthType CHECK (Type IN ('Surgery', 'Vaccination', 'Allergy'))
);

CREATE TABLE CriminalHistory (
    FileNumber CHAR(15) NOT NULL,
    Crime VARCHAR(20),
    PrisonTime INTEGER,
    CONSTRAINT pk_CriminalHistory PRIMARY KEY (FileNumber),
    CONSTRAINT fk_CriminalHistory_History FOREIGN KEY (FileNumber) REFERENCES History(FileNumber) ON DELETE CASCADE
);

CREATE TABLE House (
    Location VARCHAR(30) NOT NULL,
    Type VARCHAR(20),
    CONSTRAINT pk_House PRIMARY KEY (Location),
    CONSTRAINT c_HouseType CHECK (Type IN ('Apartment', 'House', 'Condo', 'Townhouse', 'Detached', 'Semi-Detached'))
);

CREATE TABLE Bank (
    DFI CHAR(15) NOT NULL,
    Name VARCHAR(20),
    Branch VARCHAR(20) NOT NULL,
    Location VARCHAR(30),
    CONSTRAINT pk_Bank PRIMARY KEY (DFI, Branch)
);

CREATE TABLE IRCC (
    BranchID CHAR(4) NOT NULL,
    BranchName VARCHAR(20) NOT NULL,
    CONSTRAINT pk_IRCC PRIMARY KEY (BranchID),
    CONSTRAINT c_branchName CHECK (BranchName IN ('Edmonton', 'Mississauga', 'Sydney', 'Ottawa Cpc', 'Ottawa Osc',
     'Calgary', 'Vancouver', 'Winnipeg', 'Saskatoon'))
);

CREATE TABLE BIDToLocation (
    BranchID CHAR(4) NOT NULL,
    Location VARCHAR(30),
    CONSTRAINT pk_BranchID PRIMARY KEY (BranchID),
    CONSTRAINT fk_BIDToLocation_IRCC FOREIGN KEY (BranchID) REFERENCES IRCC(BranchID) ON DELETE CASCADE
);




/*
RELATIONSHIP TABLES
*/

CREATE TABLE Applies (
    PassportNumber CHAR(8) NOT NULL,
    BranchID CHAR(4) NOT NULL,
    DateOfApplication CHAR(10),
    VisaNumber CHAR(15),
    CONSTRAINT pk_Applies PRIMARY KEY (PassportNumber),
    CONSTRAINT fk_Applies_IRCC FOREIGN KEY (BranchID) REFERENCES IRCC(BranchID) ON DELETE CASCADE,
    CONSTRAINT fk_Applies_Immigrant FOREIGN KEY (PassportNumber) REFERENCES Immigrant(PassportNumber) ON DELETE CASCADE,
    CONSTRAINT c_DOA CHECK (DateOfApplication LIKE '____-__-__')
);

CREATE TABLE HasAccount (
    PassportNumber CHAR(8) NOT NULL,
    DFI CHAR(15) NOT NULL,
    Branch VARCHAR(20),
    AccountNumber VARCHAR(3),
    Balance INTEGER,
    CreditScore INTEGER,
    Type VARCHAR(20),
    CONSTRAINT pk_HasAccount PRIMARY KEY (PassportNumber, DFI),
    CONSTRAINT fk_HasAccount_Bank FOREIGN KEY (DFI, Branch) REFERENCES Bank(DFI, Branch) ON DELETE CASCADE,
    CONSTRAINT fk_HasAccount_Immigrant FOREIGN KEY (PassportNumber) REFERENCES Immigrant(PassportNumber) ON DELETE CASCADE,
    CONSTRAINT c_AccountType CHECK (Type IN ('Checking', 'Savings', 'Credit'))
);

CREATE TABLE Has (
    PassportNumber CHAR(8),
    FileNumber CHAR(15) NOT NULL,
    CONSTRAINT pk_Has PRIMARY KEY (FileNumber),
    CONSTRAINT fk_Has_Immigrant FOREIGN KEY (PassportNumber) REFERENCES Immigrant(PassportNumber) ON DELETE CASCADE,
    CONSTRAINT fk_Has_History FOREIGN KEY (FileNumber) REFERENCES History(FileNumber) ON DELETE CASCADE
);

CREATE TABLE WillWorkAt (
    PassportNumber CHAR(8) NOT NULL,
    ContractType VARCHAR(20),
    BusinessNumber CHAR(10) NOT NULL,
    CONSTRAINT pk_WilLWorkAt PRIMARY KEY (PassportNumber),
    CONSTRAINT fk_WillWorkAt_Worker FOREIGN KEY (PassportNumber) REFERENCES Worker(PassportNumber) ON DELETE CASCADE,
    CONSTRAINT fk_WillWorkAt_Workplace FOREIGN KEY (BusinessNumber) REFERENCES Workplace(BusinessNumber) ON DELETE CASCADE,
    CONSTRAINT c_ContractType CHECK (ContractType IN ('Full Time', 'Part Time', 'Contract'))
);

CREATE TABLE GraduatedFrom (
    PassportNumber CHAR(8) NOT NULL,
    Name VARCHAR(255) NOT NULL,
    CONSTRAINT pk_GraduatedFrom PRIMARY KEY (PassportNumber, Name),
    CONSTRAINT fk_GraduatedFrom_Student FOREIGN KEY (PassportNumber) REFERENCES Student(PassportNumber) ON DELETE CASCADE,
    CONSTRAINT fk_GraduatedFrom_InternationalSchool FOREIGN KEY (Name) REFERENCES InternationalSchool(Name) ON DELETE CASCADE
);

CREATE TABLE WillStudyAt (
    PassportNumber CHAR(8) NOT NULL,
    DLI CHAR(10) NOT NULL,
    DegreeType VARCHAR(20),
    CONSTRAINT pk_WillStudyAt PRIMARY KEY (PassportNumber),
    CONSTRAINT fk_WillStudyAt_Student FOREIGN KEY (PassportNumber) REFERENCES Student(PassportNumber) ON DELETE CASCADE,
    CONSTRAINT fk_WillStudyAt_School FOREIGN KEY (DLI) REFERENCES School(DLI) ON DELETE CASCADE,
    CONSTRAINT c_DegreeType CHECK (DegreeType IN ('Bachelor', 'Master', 'PhD'))
);

CREATE TABLE WillLiveAt (
    PassportNumber CHAR(8) NOT NULL,
    Location VARCHAR(30) NOT NULL,
    Period INTEGER,
    CONSTRAINT pk_WillLiveAt PRIMARY KEY (PassportNumber),
    CONSTRAINT fk_WillLiveAt_Immigrant FOREIGN KEY (PassportNumber) REFERENCES Immigrant(PassportNumber) ON DELETE CASCADE,
    CONSTRAINT fk_WilLLiveAt_Immigrant_House FOREIGN KEY (Location) REFERENCES House(Location) ON DELETE CASCADE,
    CONSTRAINT c_Period CHECK (Period > 0)
);

/*
TRIGGERS
*/




/*
ENTITY INSERTS
*/

INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000000', 'John Doe', '2000-01-01', 'M', 'john.doe@gmail.com', 'ilikefrogs','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000001', 'Alice Smith', '1990-02-02', 'F', 'alice.smoth@gmail.com', 'ilikeamphibians','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000002', 'Bob Johnson', '1992-03-03', 'M', 'bob.johnson@gmail.com', 'iliketoads','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000003', 'Emily Brown', '1985-04-04', 'F', 'emily.brown@gmail.com', 'hoppygirl','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000004', 'David Lee', '1978-05-05', 'M', 'david.lee@gmail.com', 'ribbit123','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000005', 'Sarah Garcia', '1998-06-06', 'F', 'sarah.garcia@gmail.com', 'croak567','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000006', 'Michael Martinez', '1991-07-07', 'M', 'michael.martinez@gmail.com', 'jumpingfrogs','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000007', 'Jessica Taylor', '1980-08-08', 'F', 'jessica.taylor@gmail.com', 'hoppityhop','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000008', 'William Clark', '1976-09-09', 'M', 'william.clark@gmail.com', 'tadpole123','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000009', 'Jennifer Rodriguez', '1995-10-10', 'F', 'jennifer.rodriguez@gmail.com', 'frogsarecool','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000011', 'Alice Smith', '1990-02-02', 'F', 'alice.smoth@gmail.com', 'ilikeamphibians','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000012', 'Bob Johnson', '1992-03-03', 'M', 'bob.johnson@gmail.com', 'iliketoads','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000013', 'Charlie Williams', '1994-04-04', 'M', 'charlie.williams@gmail.com', 'iliketadpoles','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('00000014', 'John Doe', '2000-01-01', 'M', 'john.doe@gmail.com', 'ilikefrogs','R');

INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('10000000', 'Jane Doe', '2000-01-01', 'F', 'jane.doe@gmail.com', 'ilikegreen','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('10000001', 'Diana Brown', '1996-05-05', 'F', 'diana.brown@gmail.com', 'ilikeblue','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('10000002', 'Eve Davis', '1998-06-06', 'F', 'eve.davis@gmail.com', 'ilikered','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('21098765', 'Mark Johnson', '1995-03-15', 'M', 'mark.johnson@gmail.com', 'blue123','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('32987654', 'Anna Martinez', '1992-11-20', 'F', 'anna.martinez@gmail.com', 'green456','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('43876543', 'Jake Thompson', '1990-09-12', 'M', 'jake.thompson@gmail.com', 'yellow789','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('54765432', 'Sophia White', '1988-07-25', 'F', 'sophia.white@gmail.com', 'purple321','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('65654321', 'Ryan Harris', '1985-04-18', 'M', 'ryan.harris@gmail.com', 'orange654','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('76543210', 'Emma Wilson', '1982-01-30', 'F', 'emma.wilson@gmail.com', 'pink987','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('87432109', 'Daniel Garcia', '1979-10-04', 'M', 'daniel.garcia@gmail.com', 'brown123','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('98321098', 'Olivia Lopez', '1977-08-07', 'F', 'olivia.lopez@gmail.com', 'black456','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('19283746', 'Noah Rodriguez', '1974-05-19', 'M', 'noah.rodriguez@gmail.com', 'silver789','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('12345678', 'Isabella Smith', '1971-02-21', 'F', 'isabella.smith@gmail.com', 'gold321','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('23456789', 'James Brown', '1968-11-03', 'M', 'james.brown@gmail.com', 'platinum654','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('34567890', 'Charlotte Taylor', '1966-09-16', 'F', 'charlotte.taylor@gmail.com', 'diamond987','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('45678901', 'Michael Clark', '1963-06-28', 'M', 'michael.clark@gmail.com', 'sapphire123','R');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('56789012', 'Ava Lee', '1960-04-09', 'F', 'ava.lee@gmail.com', 'ruby456','R');

INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('99999999', 'Alex', '2000-01-01', 'M', 'email@gmail.com', '12345678','A');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('87654321', 'Admin2', '1992-11-20', 'F', 'admin2@gmail.com', 'admin456','A');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('65432109', 'Admin4', '1982-01-30', 'M', 'admin4@gmail.com', 'admin321','A');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('54321098', 'Admin5', '1979-10-04', 'M', 'admin5@gmail.com', 'admin654','A');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('43210987', 'Admin6', '1974-05-19', 'F', 'admin6@gmail.com', 'admin987','A');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('32109876', 'Admin7', '1971-02-21', 'F', 'admin7@gmail.com', 'admin123','A');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('10987654', 'Admin9', '1960-04-09', 'F', 'admin9@gmail.com', 'admin789','A');

INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('66666666', 'Anmol', '2003-01-16', 'M', 'anmol@gmail.com', 'anmol123','A');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('77777777', 'Shams', '2004-03-20', 'M', 'shams@gmail.com', 'shams123','A');
INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, userType) VALUES ('88888888', 'Asher', '2002-05-20', 'M', 'asher@gmail.com', 'asher123','A');

INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000000', '12312');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000001', '67890');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000002', '24680');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000003', '13579');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000004', '98765');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000005', '54321');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000006', '11223');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000007', '33445');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000008', '55667');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000009', '77889');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000011', '99011');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000012', '22334');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000013', '44556');
INSERT INTO Student (PassportNumber, StudentNumber) VALUES ('00000014', '66778');


INSERT INTO Worker (PassportNumber) VALUES ('10000000');
INSERT INTO Worker (PassportNumber) VALUES ('10000001');
INSERT INTO Worker (PassportNumber) VALUES ('10000002');
INSERT INTO Worker (PassportNumber) VALUES ('21098765');
INSERT INTO Worker (PassportNumber) VALUES ('32987654');
INSERT INTO Worker (PassportNumber) VALUES ('43876543');
INSERT INTO Worker (PassportNumber) VALUES ('54765432');
INSERT INTO Worker (PassportNumber) VALUES ('65654321');
INSERT INTO Worker (PassportNumber) VALUES ('76543210');
INSERT INTO Worker (PassportNumber) VALUES ('87432109');
INSERT INTO Worker (PassportNumber) VALUES ('98321098');
INSERT INTO Worker (PassportNumber) VALUES ('19283746');
INSERT INTO Worker (PassportNumber) VALUES ('12345678');
INSERT INTO Worker (PassportNumber) VALUES ('23456789');
INSERT INTO Worker (PassportNumber) VALUES ('34567890');
INSERT INTO Worker (PassportNumber) VALUES ('45678901');
INSERT INTO Worker (PassportNumber) VALUES ('56789012');


INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000000', 'Company Inc.', '123 Workplace Street');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000001', 'Business Corp.', '456 Workplace Avenue');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000002', 'Enterprise LLC.', '789 Workplace Boulevard');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000003', 'Firm Co.', '321 Workplace Drive');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000010', 'Bright Solutions', '222 Bright Street');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000011', 'TechCorp', '123 Tech Street');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000012', 'Innovate Solutions', '456 Innovation Avenue');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000013', 'Firm Co.', '321 Workplace Drive');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000014', 'Global Enterprises', '789 Global Plaza');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000015', 'DataWorks', '555 Data Boulevard');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000016', 'Alpha Industries', '777 Alpha Road');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000017', 'Omega Corporation', '888 Omega Street');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000018', 'Silver Solutions', '999 Silver Avenue');
INSERT INTO Workplace (BusinessNumber, Name, Location) VALUES ('0000000019', 'Gold Services', '111 Gold Lane');



INSERT INTO School (DLI, Location, Name) VALUES ('0000000000', '123 School Street', 'School Name');
INSERT INTO School (DLI, Location, Name) VALUES ('0000000001', '456 School Avenue', 'First Academy');
INSERT INTO School (DLI, Location, Name) VALUES ('0000000002', '789 School Boulevard', 'Second Institute');
INSERT INTO School (DLI, Location, Name) VALUES ('0000000003', '321 School Drive', 'Third School');
INSERT INTO School (DLI, Location, Name) VALUES ('0000000004', '654 School Road', 'Fourth Academy');
INSERT INTO School (DLI, Location, Name) VALUES ('0000000005', '987 School Lane', 'Fifth Institute');


INSERT INTO InternationalSchool (Name, Location) VALUES ('International School', '123 International Street');
INSERT INTO InternationalSchool (Name, Location) VALUES ('Global Academy', '456 Global Avenue');
INSERT INTO InternationalSchool (Name, Location) VALUES ('Worldwide Institute', '789 Worldwide Boulevard');
INSERT INTO InternationalSchool (Name, Location) VALUES ('Universal School', '321 Universal Drive');
INSERT INTO InternationalSchool (Name, Location) VALUES ('Planetwide School', '654 Planetwide Road');
INSERT INTO InternationalSchool (Name, Location) VALUES ('Cosmopolitan Academy', '987 Cosmopolitan Lane');


INSERT INTO History (FileNumber, DateOfEvent, ExpiryDate, CertifyingInstitution) VALUES ('000000000000000', '2022-01-01', '2023-01-01', 'M Institution Name');
INSERT INTO HealthHistory (FileNumber, Type, Description) VALUES ('000000000000000', 'Surgery', 'Appendix Removal');

INSERT INTO History (FileNumber, DateOfEvent, ExpiryDate, CertifyingInstitution) VALUES ('000000000000001', '2022-02-02', '2023-02-02', 'M Institution Name');
INSERT INTO HealthHistory (FileNumber, Type, Description) VALUES ('000000000000001', 'Surgery', 'Knee Replacement');

INSERT INTO History (FileNumber, DateOfEvent, ExpiryDate, CertifyingInstitution) VALUES ('000000000000002', '2022-03-03', '2023-03-03', 'M Institution Name');
INSERT INTO HealthHistory (FileNumber, Type, Description) VALUES ('000000000000002', 'Surgery', 'Hip Replacement');

INSERT INTO History (FileNumber, DateOfEvent, ExpiryDate, CertifyingInstitution) VALUES ('000000000000003', '2022-04-04', '2023-04-04', 'M Institution Name');
INSERT INTO HealthHistory (FileNumber, Type, Description) VALUES ('000000000000003', 'Surgery', 'Heart Surgery');


INSERT INTO History (FileNumber, DateOfEvent, ExpiryDate, CertifyingInstitution) VALUES ('100000000000000', '2022-01-01', '2023-01-01', 'C Institution Name');
INSERT INTO CriminalHistory (FileNumber, Crime, PrisonTime) VALUES ('100000000000000', 'Theft', 6);

INSERT INTO History (FileNumber, DateOfEvent, ExpiryDate, CertifyingInstitution) VALUES ('100000000000001', '2022-05-05', '2023-05-05', 'C Institution Name');
INSERT INTO CriminalHistory (FileNumber, Crime, PrisonTime) VALUES ('100000000000001', 'Fraud', 2);

INSERT INTO History (FileNumber, DateOfEvent, ExpiryDate, CertifyingInstitution) VALUES ('100000000000002', '2022-06-06', '2023-06-06', 'C Institution Name');
INSERT INTO CriminalHistory (FileNumber, Crime, PrisonTime) VALUES ('100000000000002', 'Burglary', 1);

INSERT INTO House (Location, Type) VALUES ('123 Lane', 'Apartment');
INSERT INTO House (Location, Type) VALUES ('456 Avenue', 'Townhouse');
INSERT INTO House (Location, Type) VALUES ('789 Boulevard', 'Condo');
INSERT INTO House (Location, Type) VALUES ('321 Drive', 'Detached');
INSERT INTO House (Location, Type) VALUES ('654 Road', 'Semi-Detached');
INSERT INTO House (Location, Type) VALUES ('987 Lane', 'Apartment');
INSERT INTO House (Location, Type) VALUES ('123 Main Street', 'House');
INSERT INTO House (Location, Type) VALUES ('456 Elm Avenue', 'Condo');
INSERT INTO House (Location, Type) VALUES ('789 Oak Drive', 'Apartment');
INSERT INTO House (Location, Type) VALUES ('101 Pine Street', 'Townhouse');
INSERT INTO House (Location, Type) VALUES ('202 Maple Court', 'Detached');
INSERT INTO House (Location, Type) VALUES ('303 Cedar Lane', 'Semi-Detached');
INSERT INTO House (Location, Type) VALUES ('404 Birch Road', 'House');
INSERT INTO House (Location, Type) VALUES ('505 Willow Circle', 'Condo');
INSERT INTO House (Location, Type) VALUES ('606 Spruce Place', 'Apartment');
INSERT INTO House (Location, Type) VALUES ('707 Juniper Lane', 'Townhouse');


INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('123456789012345', 'Spam Bank', 'Meat Branch', '123 Street');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('123456789012345', 'Toast Bank', 'Wheat Branch', '654 Road');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('234567890123456', 'Spam Bank', 'Goat Branch', '456 Avenue');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('345678901234567', 'Bacon Bank', 'Cow Branch', '789 Boulevard');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('456789012345678', 'Sausage Bank', 'Dog Branch', '321 Drive');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('456789012345678', 'Coffee Bank', 'Drink Branch', '987 Lane');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('123456789012345', 'Toast Bank', 'Grain Branch', '654 Road');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('987654321098765', 'Lakeside Bank', 'Waterfront Branch', '789 Lake Avenue');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('567890123456789', 'Summit Bank', 'Mountain View Branch', '123 Summit Street');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('345678901234567', 'City Bank', 'Downtown Branch', '456 Main Street');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('901234567890123', 'Harbor Trust', 'Port Branch', '321 Harbor Drive');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('234567890123456', 'Golden Bank', 'Bridge Branch', '789 Bridge Road');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('789012345678901', 'Pine Union', 'Forest Branch', '101 Forest Avenue');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('678901234567890', 'Sunrise Bank', 'Sunrise Branch', '555 Sunrise Boulevard');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('012345678901234', 'Metro Union', 'Metro Center Branch', '777 Metro Street');
INSERT INTO Bank (DFI, Name, Branch, Location) VALUES ('456789012345678', 'Silver Bank', 'Silver Branch', '888 Silver Street');

INSERT INTO IRCC (BranchID, BranchName) VALUES ('1234', 'Vancouver');
INSERT INTO IRCC (BranchID, BranchName) VALUES ('2345', 'Edmonton');
INSERT INTO IRCC (BranchID, BranchName) VALUES ('3456', 'Mississauga');
INSERT INTO IRCC (BranchID, BranchName) VALUES ('4567', 'Sydney');
INSERT INTO IRCC (BranchID, BranchName) VALUES ('5678', 'Ottawa Osc');
INSERT INTO IRCC (BranchID, BranchName) VALUES ('6789', 'Winnipeg');
INSERT INTO IRCC (BranchID, BranchName) VALUES ('6782', 'Calgary');
INSERT INTO IRCC (BranchID, BranchName) VALUES ('3678', 'Ottawa Cpc');
INSERT INTO IRCC (BranchID, BranchName) VALUES ('4569', 'Saskatoon');

INSERT INTO BIDToLocation (BranchID, Location) VALUES ('1234', '123 Street');
INSERT INTO BIDToLocation (BranchID, Location) VALUES ('2345', '234 Street');
INSERT INTO BIDToLocation (BranchID, Location) VALUES ('3456', '345 Street');
INSERT INTO BIDToLocation (BranchID, Location) VALUES ('4567', '456 Street');
INSERT INTO BIDToLocation (BranchID, Location) VALUES ('5678', '567 Street');
INSERT INTO BIDToLocation (BranchID, Location) VALUES ('6789', '678 Street');
INSERT INTO BIDToLocation (BranchID, Location) VALUES ('6782', '987 Street');
INSERT INTO BIDToLocation (BranchID, Location) VALUES ('3678', '917 Street');
INSERT INTO BIDToLocation (BranchID, Location) VALUES ('4569', '927 Street');


/*
RELATIONSHIPS
*/

INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000000', '1234', '2022-01-01', '123456789012345');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000001', '2345', '2022-01-01', '234567890123456');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000002', '3456', '2022-01-01', '345678901234567');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000003', '4567', '2022-01-01', '456789012345678');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000004', '5678', '2022-01-01', '567890123456789');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000005', '6789', '2022-01-01', '678901234567890');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000006', '6782', '2022-01-01', '789012345678901');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000007', '3678', '2023-01-15', '223456789012345');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000008', '4569', '2023-02-20', '334567890123456');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000009', '1234', '2023-03-25', '445678901234567');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000011', '2345', '2023-04-30', '156789012345678');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000012', '3456', '2023-05-05', '167890123456789');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000013', '4567', '2023-06-10', '178901234567890');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('00000014', '1234', '2023-07-15', '189012345678901');


INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('10000000', '6789', '2023-08-20', '810123456789012');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('10000001', '3678', '2023-09-25', '921234567890123');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('10000002', '1234', '2023-10-30', '032345678901234');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('21098765', '6789', '2023-08-20', '840123456789012');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('32987654', '3456', '2023-09-25', '951234567890123');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('43876543', '3678', '2023-10-30', '062345678901234');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('54765432', '6789', '2023-08-20', '870123456789012');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('65654321', '3678', '2023-09-25', '981234567890123');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('76543210', '1234', '2023-10-30', '092345678901234');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('87432109', '6789', '2023-08-20', '820123456789012');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('98321098', '6789', '2023-09-25', '911234567890123');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('19283746', '3678', '2023-10-30', '012345678901234');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('12345678', '6789', '2023-08-20', '820123456789012');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('23456789', '3456', '2023-09-25', '941234567890123');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('34567890', '1234', '2023-10-30', '042345678901234');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('45678901', '3678', '2023-10-30', '042345678901234');
INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ('56789012', '1234', '2023-10-30', '012345678901234');



INSERT INTO HasAccount (PassportNumber, DFI, Branch, AccountNumber, Balance, CreditScore, Type) VALUES ('00000000', '123456789012345', 'Meat Branch', '123', 1000, 700, 'Checking');
INSERT INTO HasAccount (PassportNumber, DFI, Branch, AccountNumber, Balance, CreditScore, Type) VALUES ('00000001', '123456789012345', 'Grain Branch', '234', 1000, 700, 'Savings');
INSERT INTO HasAccount (PassportNumber, DFI, Branch, AccountNumber, Balance, CreditScore, Type) VALUES ('00000002', '123456789012345', 'Grain Branch', '345', 1000, 700, 'Credit');
INSERT INTO HasAccount (PassportNumber, DFI, Branch, AccountNumber, Balance, CreditScore, Type) VALUES ('00000003', '234567890123456', 'Goat Branch', '123', 1000, 700, 'Checking');

INSERT INTO HasAccount (PassportNumber, DFI, Branch, AccountNumber, Balance, CreditScore, Type) VALUES ('10000000', '345678901234567', 'Cow Branch', '123', 1000, 700, 'Checking');
INSERT INTO HasAccount (PassportNumber, DFI, Branch, AccountNumber, Balance, CreditScore, Type) VALUES ('10000001', '456789012345678', 'Drink Branch', '123', 1000, 700, 'Savings');
INSERT INTO HasAccount (PassportNumber, DFI, Branch, AccountNumber, Balance, CreditScore, Type) VALUES ('10000002', '456789012345678', 'Silver Branch', '234', 1000, 700, 'Checking');

INSERT INTO Has (PassportNumber, FileNumber) VALUES ('00000000', '000000000000000');
INSERT INTO Has (PassportNumber, FileNumber) VALUES ('00000001', '100000000000000');
INSERT INTO Has (PassportNumber, FileNumber) VALUES ('00000002', '100000000000001');
INSERT INTO Has (PassportNumber, FileNumber) VALUES ('00000003', '000000000000001');

INSERT INTO Has (PassportNumber, FileNumber) VALUES ('10000000', '000000000000002');
INSERT INTO Has (PassportNumber, FileNumber) VALUES ('10000001', '100000000000002');
INSERT INTO Has (PassportNumber, FileNumber) VALUES ('10000002', '000000000000003');

INSERT INTO WillWorkAt (PassportNumber, ContractType, BusinessNumber) VALUES ('10000000', 'Full Time', '0000000000');
INSERT INTO WillWorkAt (PassportNumber, ContractType, BusinessNumber) VALUES ('10000001', 'Part Time', '0000000001');
INSERT INTO WillWorkAt (PassportNumber, ContractType, BusinessNumber) VALUES ('10000002', 'Contract', '0000000002');

INSERT INTO GraduatedFrom (PassportNumber, Name) VALUES ('00000000', 'International School');
INSERT INTO GraduatedFrom (PassportNumber, Name) VALUES ('00000000', 'Global Academy');

INSERT INTO WillStudyAt (PassportNumber, DLI, DegreeType) VALUES ('00000000', '0000000000', 'Bachelor');
INSERT INTO WillStudyAt (PassportNumber, DLI, DegreeType) VALUES ('00000001', '0000000000', 'Bachelor');
INSERT INTO WillStudyAt (PassportNumber, DLI, DegreeType) VALUES ('00000002', '0000000000', 'Master');
INSERT INTO WillStudyAt (PassportNumber, DLI, DegreeType) VALUES ('00000003', '0000000000', 'PhD');

INSERT INTO WillLiveAt (PassportNumber, Location, Period) VALUES ('00000000', '123 Lane', 36);
INSERT INTO WillLiveAt (PassportNumber, Location, Period) VALUES ('00000001', '123 Lane', 36);
INSERT INTO WillLiveAt (PassportNumber, Location, Period) VALUES ('00000002', '654 Road', 48);
INSERT INTO WillLiveAt (PassportNumber, Location, Period) VALUES ('00000003', '321 Drive', 72);

INSERT INTO WillLiveAt (PassportNumber, Location, Period) VALUES ('10000000', '456 Avenue', 12);
INSERT INTO WillLiveAt (PassportNumber, Location, Period) VALUES ('10000001', '456 Avenue', 15);
INSERT INTO WillLiveAt (PassportNumber, Location, Period) VALUES ('10000002', '789 Boulevard', 48);